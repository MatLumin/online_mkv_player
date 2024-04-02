

<?php



define("DEFAULT_BYTE_OFFSET", 0);
define("DEFUALT_MIME_TYPE", 'application/octet-stream');
define("REGEX_PATTERN__CONTENT_RANGE_HEADER_EXISTENCE", '%bytes=(\d+)-(\d+)?%i');
define("PARTIAL_CONTENT_HEADER", "HTTP/1.1 206 Partial Content");
define("_1Kb", 1024);
define("DEFAULT_BUFFER_SIZE", _1Kb*32);

function remove_problemtaic_headers()
	{
	header_remove('Cache-Control');
	header_remove('Pragma');	
	}


function serve_file_partially($file_name, $file_title = null, $content_type = DEFUALT_MIME_TYPE)
	{
	$file_path = "mkv_videos";
	$does_file_exist = file_exists($file_path);
	$is_file_readable = is_readable($file_path);
	if($does_file_exist == false)
		{
		echo "file not found";
		exit();
		}

	if($is_file_readable == false)
		{
		echo "file is not read able";
		exit();
		}

	remove_problemtaic_headers();


	$byte_offset = DEFAULT_BYTE_OFFSET;
	$byte_length = filesize($file_path);
	$file_size_in_bytes = filesize($file_path);


	header('Accept-Ranges: bytes', true);
	header('Content-Type: ' . $content_type, true);

	if( $file_title )
		$header_to_add = sprintf('Content-Disposition: attachment; filename="%s"',$file_title);
		header($header_to_add);

	$requested_range_from_client =  $_SERVER['HTTP_RANGE'];
	$is_byte_range_defined_in_request = isset($requested_range_from_client);
	$is_there_any_byte_range_related_header_in_request = preg_match(REGEX_PATTERN__CONTENT_RANGE_HEADER_EXISTENCE, $requested_range_from_client, $match) == 1;
	if($is_byte_range_defined_in_request && $is_there_any_byte_range_related_header_in_request)
		{
		$byte_offset = (int)$match[1];
		$is_finsih_byte_defined = isset($match[2]);
		if($is_finsih_byte_defined)
			{
			$finish_bytes = (int)$match[2];
	        $byte_length = $finish_bytes+1;
			} 
		else 
			{
			$finish_bytes = $file_size_in_bytes-1;
			}
	

		$content_range_header = sprintf('Content-Range: bytes %d-%d/%d', $byte_offset, $finish_bytes, $file_size_in_bytes);
		header(PARTIAL_CONTENT_HEADER);
		header($content_range_header);
		}



	$byte_range = $byte_length - $byte_offset;

	header(sprintf('Content-Length: %d', $byte_range));
	header(sprintf('Expires: %s', date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT'));

	$buffer = '';
	$bufferSize = DEFAULT_BUFFER_SIZE;
	$byte_pool = $byte_range;

	$handle = fopen($file_path, 'r');

	$result_of_file_cursor_setting = fseek($handle, $byte_offset, SEEK_SET);
	if($result_of_file_cursor_setting == -1 )
		echo "file cursor setting failed";


	while( $byte_pool != 0 )
		{
		$current_chunk_size = min($bufferSize, $byte_pool); 
		$current_chunk = fread($handle, $current_chunk_size);
		$current_actual_chuck_size = strlen($buffer);
		$buffer = $current_chunk; 
		$we_read_nothing = $current_actual_chuck_size == 0;
		if($we_read_nothing)
			{
			echo "chuck size eunexpectedly dropped to zero :|";
			exit();
			}

		
		$byte_pool -= $current_actual_chuck_size;

		### Write the buffer to output
		echo $buffer;
		flush();
		}

	exit();
	}


$target_file_name = $_GET['requested_file'];
$content_tyoe = $_SERVER["CONTENT_TYPE"];
serve_file_partially($target_file_name, $target_file_name, $content_tyoe)
?>
