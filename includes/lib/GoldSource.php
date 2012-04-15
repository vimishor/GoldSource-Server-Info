<?php if ( ! defined('GS_LOAD')) exit('No direct script access allowed');

/**
 * @package		GoldSrc_Live_Status
 * @author		www.gentle.ro
 * @copyright	Copyright (c) 2010 - 2011, Gentle.ro 
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link		http://www.gentle.ro/proiecte/goldsource-live-status/
 */
 
// ------------------------------------------------------------------------

/**
 * Class GoldSource
 *
 * Get server and players details. 
 *
 * @see         http://github.com/koraktor/steam-condenser/
 * @package		GoldSrc_Server_Info
 * @subpackage  Core
 */
class GoldSource
{    
    protected $_messages  = array(
        '100' => 'OK',
        '101' => 'Connection timeout.',
        '102' => 'Not connected.',
        '103' => 'Can\'t connect to specifed address.',
        '104' => 'Invalid address.',
        '105' => 'Game unknown.'
    );
    
    private $timeout = 3;
        
    // server data
    private $ip;
    private $port;
    private $protocol;
    
    // read pointer position
    protected $index;
    
    // ---
    protected $resource;
    protected $byte_array;
    protected $challenge;
    
    public function __construct($address, $port)
    {
        if ($this->validate_address($address) === false)
        {
            return 104;
        }
                
        $this->ip   = $address;
        $this->port = $port;
        
        if (!$this->connect())
            return 103;
        
        $this->protocol = 0;
        $this->challenge = false;
    }
    
    public function get_message($code)
    {
        return $this->_messages[$code];
    }
    
    public function get_geoMap()
    {
        $location = json_decode($this->get_geoLocation());   
        $zoom = ($location->city != '') ? 6 : 2;
        
        return '<img src="http://maps.googleapis.com/maps/api/staticmap?center='.$location->latitude.','.$location->longitude.'&zoom='.$zoom.'&size=300x240&sensor=false&&markers=size:mid|color:red|'.$location->city.','.$location->country_name.'">';
    }
    
    public function get_geoLocation()
    {
        $data = file_get_contents('http://freegeoip.net/json/'.$this->ip);
                
        return $data;
    }
    
    /**
     * Get server challenge and saves it in $this->challenge
     * 
     * @access  private
     */    
    private function get_challenge()
    {
        if (!$this->is_connected())
            die('not connected');
        
        if ( $this->challenge !== false)
            return $this->challenge;
        
        if ($this->protocol == 48)
        {
            $this->write_data("\xFF\xFF\xFF\xFFU\xFF\xFF\xFF\xFF\x00");
            $tmp = fread($this->resource, 1400);
            if( ($tmp[ 4 ] == 'A') && (substr( $tmp, 0, 5 ) == "\xFF\xFF\xFF\xFFA") )
            {
                $this->challenge = substr( $tmp, 5 );
            }            
        }
        else
        {
            $this->challenge = "-1";
        }
    }
    
    /**
     * Fetch players info
     * 
     * @see http://developer.valvesoftware.com/wiki/Source_Server_Queries
     * @see $_messages
     * 
     * @access public
     * @return array
     */
    public function get_players()
    {
        if ($this->validate_address($this->ip) === false)
        {
            return 104;
        }
        
        $this->get_challenge();
            
        $this->write_data("\xFF\xFF\xFF\xFF\x55".$this->challenge);
        
        $this->byte_array   = fread($this->resource, 4096);
        $status             = socket_get_status($this->resource);
                       
        if ($status['eof'] == 1)
            return 103;
                
        if ($status['timed_out'])
            return 101;
            
        $this->index = 0;   // reset position
        $this->skip(4);     // skip 5 bytes
        
        $type               = $this->getbyte(); // should be equal to D
        $online_players     = $this->getbyte();

        $players = array();
                        
        for($i=0;$i<$online_players;$i++)
		{
            $players[$i]['id']       = $this->getbyte(); 
            $players[$i]['nick']     = htmlentities($this->getstring());
            $players[$i]['score']    = $this->getlong();
            $players[$i]['time_int'] = (int)$this->getfloat();
            $players[$i]['time_gmt'] =  GMDate( ( ($players[$i]['time_int']) > 3600 ? "H:i:s" : "i:s" ), $players[$i]['time_int'] );
        }
        return $players;
    }
    
    /**
     * Fetch server info
     * 
     * @see http://developer.valvesoftware.com/wiki/Source_Server_Queries
     * @see $_messages
     * 
     * @access public
     * @return array
     */
    public function get_details()
    {
        if ($this->validate_address($this->ip) === false)
        {
            return 104;
        }
        
        $start = $this->getTime();
        $this->write_data("\xFF\xFF\xFF\xFFTSource Engine Query\x00");
        
        $this->byte_array   = fread($this->resource, 4096);
        $status             = socket_get_status($this->resource);
        
        if ($status['eof'] == 1)
            return 103;
                
        if ($status['timed_out'])
            return 101;
            
        $stop               = $this->getTime();
        $server_ping        = (int)( ($stop-$start)*1000 );
        
        $this->index = 0;   // reset position
        $this->skip(4);     // skip 4 bytes
        $type               = $this->getchar();
        $server             = array();
        
        if ($type == 'm') // protocol 47
        {            
            $this->getstring(); // equal to loopback. useless!
            $server['address']       = $this->ip.':'.$this->port;
            $server['hostname']      = $this->getstring();
            $server['map']           = $this->getstring();
            $server['dir']           = $this->getstring();
            $server['desc']          = $this->getstring();
            $server['appid']         = 10;
            $server['players_on']    = $this->getbyte();
            $server['players_max']   = $this->getbyte();
            $server['protocol']      = $this->getbyte();
            $server['type']          = $this->getchar();
            $server['os']            = $this->getchar();
            $server['password']      = $this->getbyte();
            $server['is_mod']        = ($this->getbyte() == 1) ? true : false; 
            
            if ($server['is_mod'])
            {
                $server['mod']['url_info']  = $this->getstring();
                $server['mod']['url_down']  = $this->getstring();
                $this->getbyte(); // skip null
                $server['mod']['version']   = $this->getlong();
                $server['mod']['size']      = $this->getlong();
                $server['mod']['sv_only']   = $this->getbyte();
                $server['mod']['cl_dll']    = $this->getbyte();
            }
            
            $server['secure']        = ($this->getbyte() == 1) ? true : false;
            $server['bots']          = $this->getbyte();     
            $server['ping']          = $server_ping;
            $this->protocol          = $server['protocol'];
        }
        elseif ($type == 'I') // protocol 48
        {
            $server['address']        = $this->ip.':'.$this->port;
            $server['protocol']       = $this->getbyte();
            $server['hostname']       = $this->getstring();
            $server['map']            = $this->getstring();
            $server['dir']            = $this->getstring();
            $server['desc']           = $this->getstring();
            $server['appid']          = $this->getshort(); 
            $server['players_on']     = $this->getbyte();
            $server['players_max']    = $this->getbyte();
            $server['bots']           = $this->getbyte();
            $server['type']           = $this->getchar();
            $server['os']             = $this->getchar();
            $server['password']       = $this->getbyte();
            $server['secure']         = $this->getbyte();
            $server['is_mod']         = false;
            $server['ping']           = $server_ping;
            $server['version']        = $this->getstring();
            
            $extra                                  = $this->getbyte();
            if ($extra & 0x80)
            {
                $server['port']       = $this->getshort();
            }
            
            if ($extra & 0x10)
            {
                $server['id']         = $this->getUnsignedLong() | ($this->getUnsignedLong() << 32);
            }
            
            if ($extra & 0x40)
            {
                $server['tv_port']    = $this->getshort();
                $server['tv_name']    = $this->getstring();
            }
            
            if ($extra & 0x20)
            {
                $server['tags']       = $this->getstring();
            }
            
            $this->protocol           = $server['protocol'];
        }
        else
        {
            $server['error']          = 105;
        }
        
        return $server;
    } 
    
    /**
     * Write data to socket
     * 
     * @access  private
     * @return  bool
     */
    private function write_data($command)
    {       
        return ( fwrite($this->resource, $command, strlen($command)) === false) ? false : true; 
    }
    
    /**
     * Validate DNS, IP and transforms DNS in IPV4.
     * 
     * @access  public
     * @param   string  $address    DNS or IP to validate
     * @return  bool    False on error
     */
    public function validate_address($address)
    {
        // if there is no valid DNS or IP
        if (!$this->is_dns($address) AND !$this->is_ip($address) )
            return false;
        
        // if there is a valid DNS, transform in IPV4 address
        if ($this->is_dns($address))
            $this->ip = gethostbyname($address);
        
        return true;
    }
    
    /**
     * Simple DNS validation
     * 
     * @access  public
     * @param   string  $domain Domain to check
     * @return  bool    False on error
     */    
    public function is_dns($domain)
    {
        return (preg_match('/^([a-z0-9]([-a-z0-9]*[a-z0-9])?\\.)+((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$/i', $domain) === 0) ? false : true;
    }
    
    /**
	 * Validate string as IP (IPV4)
	 *
     * @access private
	 * @param  string  $ip_addr String to be validated
	 * @return bool
	 */
	private function is_ip($ip_addr)
	{
		if (preg_match("/^(\d{1,3})\.$/", $ip_addr) || preg_match("/^(\d{1,3})\.(\d{1,3})$/",
			$ip_addr) || preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip_addr) ||
			preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip_addr))
		{
			$parts = explode(".", $ip_addr);
			
			foreach ($parts as $ip_parts)
			{
				if (intval($ip_parts) > 255 || intval($ip_parts) < 0)
					return false; //if number is not within range of 0-255
			}
			return true;
		}
		else
			return false;
	}
    
    /**
     * Read specified amount of bytes from buffer
     * 
     * @access  private
     * @param   int     $length Bytes number
     * @return  string
     */
    private function get($length)
    {
        $data = substr($this->byte_array, $this->index, $length);
        $this->index += $length;
        
        return $data;
    }
    
    /**
     * Read 1 byte from buffer
     * 
     * @access  private
     * @return  int 
     */
    private function getbyte()
	{
		return ord($this->get(1));
	}
    
    /**
     * Read a long integer from buffer
     * 
     * @access  private
     * @return  long
     */
    private function getlong()
    {
        $data = unpack('l', $this->get(4));

        return $data[1];
    }
    
    /**
     * Read a floating point number from buffer
     * 
     * @access  private
     * @return  float
     */
    private function getfloat()
    {
        $data = unpack('f', $this->get(4));
        
        return $data[1];
    }
    
    /**
     * Read a short integer from buffer
     * 
     * @access  private
     * @return  short
     */
    private function getshort()
    {
        $data = unpack('v', $this->get(2));

        return $data[1];
    }
    
    /**
     * Read a zero-byte terminated string from buffer
     * 
     * @access  private
     * @return  string
     */
    private function getstring()
    {
        $tmp = strpos($this->byte_array, "\0", $this->index);
        
        if ($tmp === false)
        {
            return '';
        }
        else
        {
            $string = $this->get($tmp - $this->index);
            $this->index++;
            
            return $string;
        }
    }
    
    /**
     * Read a char from buffer
     * 
     * @access  private
     * @return  string
     */
    private function getchar()
    {
        return substr($this->byte_array,$this->index++,1);
    }
    
    private function getUnsignedLong()
    {
        $data = unpack('V', $this->get(4));
        
        return $data[1];
    }
    
    /**
     * Skip specified bytes number
     */
    private function skip($length)
	{
		$this->index += $length;
	}
    
    /**
     * Calculate the current time based on microtime
     * 
     * @access  public
     * @return  float   Current time
     */
    public function getTime()
    {
        $t = explode(' ', microtime());
        return (float)$t[0] + (float)$t[1];
    }
        
    /**
     * Check current connection state
     * 
     * @access  public
     * @return  bool
     */
    public function is_connected()
    {
        return (is_resource($this->resource)) ? true : false;
    } 
    
    
    /**
     * Initiate connection to server
     * 
     * @access  public
     * 
     * @param   string  $address    Server hostname or ip
     * @param   int     $port       Server port
     * @return  bool    False on failure.
     */
	public function connect()
	{
		$this->disconnect();
		
        if ( $this->resource = @fsockopen('udp://'. $this->ip, $this->port) )
        {
            socket_set_timeout($this->resource, $this->timeout);
        }
        
        return $this->is_connected();
	}
    
    /**
     * Close active connection
     */
    public function disconnect()
    {
        if ($this->is_connected())
        {
            fclose($this->resource);
        }
    }
    
    /**
     * Clean data before close
     */
	public function __destruct()
	{
		$this->disconnect();
	}
}