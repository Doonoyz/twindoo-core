<?php
/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_Zip {
	/**
	 * Array to store options for file streaming
	 *
	 * @var array $datasec
	 */
	private $_streamOptions = array(
		'name' => 'file',
		'extension' => 'zip',
		'content-type' => 'application/zip',
		);
	
	/**
	 * Array to store compressed data
	 *
	 * @var array
	 */
	private $_datasec	  = array();

	/**
	 * Central directory
	 *
	 * @var array
	 */
	private $_ctrl_dir	 = array();

	/**
	 * End of central directory record
	 *
	 * @var string
	 */
	private $_eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

	/**
	 * Last offset position
	 *
	 * @var int
	 */
	private $_old_offset   = 0;


	/**
	 * Converts an Unix timestamp to a four byte DOS date and time format (date
	 * in high two bytes, time in low two bytes allowing magnitude comparison).
	 *
	 * @param int the current Unix timestamp
	 *
	 * @return int the current date in a four byte DOS format
	 */
	public function unix2DosTime($unixtime = 0) {
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

		if ($timearray['year'] < 1980) {
			$timearray['year']	= 1980;
			$timearray['mon']	 = 1;
			$timearray['mday']	= 1;
			$timearray['hours']   = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		} // end if

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
				($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}


	/**
	 * Adds "file" to archive
	 *
	 * @param string file contents
	 * @param string name of the file in the archive (may contains the path)
	 * @param int    the current timestamp
	 */
	public function addFile($data, $name, $time = 0) {
		$name	 = str_replace('\\', '/', $name);

		$dtime	= dechex($this->unix2DosTime($time));
		$hexdtime = '\x' . $dtime[6] . $dtime[7]
				  . '\x' . $dtime[4] . $dtime[5]
				  . '\x' . $dtime[2] . $dtime[3]
				  . '\x' . $dtime[0] . $dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		$fr   = "\x50\x4b\x03\x04";
		$fr   .= "\x14\x00";			// ver needed to extract
		$fr   .= "\x00\x00";			// gen purpose bit flag
		$fr   .= "\x08\x00";			// compression method
		$fr   .= $hexdtime;			 // last mod time and date

		// "local file header" segment
		$unc_len = strlen($data);
		$crc	 = crc32($data);
		$zdata   = gzcompress($data);
		$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
		$c_len   = strlen($zdata);
		$fr	  .= pack('V', $crc);			 // crc32
		$fr	  .= pack('V', $c_len);		   // compressed filesize
		$fr	  .= pack('V', $unc_len);		 // uncompressed filesize
		$fr	  .= pack('v', strlen($name));	// length of filename
		$fr	  .= pack('v', 0);				// extra field length
		$fr	  .= $name;

		// "file data" segment
		$fr .= $zdata;

		// "data descriptor" segment (optional but necessary if archive is not
		// served as file)
		// nijel(2004-10-19): this seems not to be needed at all and causes
		// problems in some cases (bug #1037737)
		//$fr .= pack('V', $crc);				 // crc32
		//$fr .= pack('V', $c_len);			   // compressed filesize
		//$fr .= pack('V', $unc_len);			 // uncompressed filesize

		// add this entry to array
		$this -> _datasec[] = $fr;

		// now add to central directory record
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";				// version made by
		$cdrec .= "\x14\x00";				// version needed to extract
		$cdrec .= "\x00\x00";				// gen purpose bit flag
		$cdrec .= "\x08\x00";				// compression method
		$cdrec .= $hexdtime;				 // last mod time & date
		$cdrec .= pack('V', $crc);		   // crc32
		$cdrec .= pack('V', $c_len);		 // compressed filesize
		$cdrec .= pack('V', $unc_len);	   // uncompressed filesize
		$cdrec .= pack('v', strlen($name)); // length of filename
		$cdrec .= pack('v', 0);			 // extra field length
		$cdrec .= pack('v', 0);			 // file comment length
		$cdrec .= pack('v', 0);			 // disk number start
		$cdrec .= pack('v', 0);			 // internal file attributes
		$cdrec .= pack('V', 32);			// external file attributes - 'archive' bit set

		$cdrec .= pack('V', $this -> _old_offset); // relative offset of local header
		$this -> _old_offset += strlen($fr);

		$cdrec .= $name;

		// optional extra field, file comment goes here
		// save to central directory
		$this -> _ctrl_dir[] = $cdrec;
	}


	/**
	 * Dumps out file
	 *
	 * @return string the zipped file
	 */
	public function file() {
		$data	= implode('', $this -> _datasec);
		$ctrldir = implode('', $this -> _ctrl_dir);

		return
			$data .
			$ctrldir .
			$this -> _eof_ctrl_dir .
			pack('v', sizeof($this -> _ctrl_dir)) .  // total # of entries "on this disk"
			pack('v', sizeof($this -> _ctrl_dir)) .  // total # of entries overall
			pack('V', strlen($ctrldir)) .		   // size of central dir
			pack('V', strlen($data)) .			  // offset to start of central dir
			"\x00\x00";							 // .zip file comment length
	}
	
	/**
	 * Streams the file to the browser
	 *
	 * @param array $options Options for file transmission
	 */
	public function stream($options = array()) {
		$this->_streamOptions = array_merge($this->_streamOptions, $options);
		set_time_limit(0);
		@apache_setenv('no-gzip', '1');
		@ini_set('zlib.output_compression', '0');
		@ini_set('implicit_flush', '1');
		for ($i = 0; $i < ob_get_level(); $i++) {
			ob_end_flush();
		}
		ob_implicit_flush(1);
		ob_start();
		$content = $this->file();
		header ( 'Content-disposition: attachment; filename=' . rawurlencode ( $this->_streamOptions['name'] ) . '.' . $this->getOriginalExt ($this->_streamOptions['extension']) );
		header ( 'Content-Type: ' . $this->_streamOptions['content-type'] );
		header ( 'Content-Transfer-Encoding: file' );
		header ( 'Content-Length: ' . strlen($content) );
		header ( 'Pragma: no-cache' );
		header ( 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
		header ( 'Expires: 0' );
		ob_flush();
		flush();
		echo $content;
	}
}