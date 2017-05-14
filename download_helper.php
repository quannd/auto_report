<?php

/**
 * Force Download
 *
 * Generates headers that force a download to happen
 *
 * @access	public
 * @param	string	filename
 * @param	mixed	the data to be downloaded
 * @return	void
 */
if (!function_exists('force_download')) {

    function force_download($filename = '', $data = '', $mime_type = '') {
        if ($filename == '' OR $data == '') {
            return FALSE;
        }

        // Try to determine if the filename includes a file extension.
        // We need it in order to set the MIME type
        if (FALSE === strpos($filename, '.')) {
            return FALSE;
        }

        // Grab the file extension
        $x = explode('.', $filename);
        $extension = end($x);

        // Load the mime types
        if (is_file('/mimes.php')) {
            include('/mimes.php');
        }

        // Set a default mime if we can't find it
        if (!isset($mimes[$extension])) {
            $mime = 'application/octet-stream';
        } else {
            $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
        }

        // Generate the server headers
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE) {
            if ($mime_type != '') {
                header('Content-Type: "' . $mime_type . '"');
            } else {
                header('Content-Type: "' . $mime . '"');
            }
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: " . strlen($data));
        } else {
            if ($mime_type != '') {
                header('Content-Type: "' . $mime_type . '"');
            } else {
                header('Content-Type: "' . $mime . '"');
            }
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: no-cache');
            header("Content-Length: " . strlen($data));
        }

        exit($data);
    }

}


/* End of file download_helper.php */
/* Location: ./system/helpers/download_helper.php */