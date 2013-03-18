<?php

/**
 * URL Shortener Class
 *
 * @author Mark Roland
 * @copyright Mark Roland, 9/28/2011
 *
 **/
class urlShortener {

	/**
	 * Turn a long URL into a short URL
	 * @param string $shortcut The "key" used to redirect to the $destination URL
	 * @param string $destination Any valid URL
	 * @param string $set_referrer Whether or not the HTTP referrer needs to be preserved
	 * @return boolean Results of shortcut create
	 */
	function create_shortcut($shortcut, $destination, $set_referrer = 0){
		$query = sprintf(
			"INSERT INTO `short_url`
			SET shortcut = '%s', `destination_url` = '%s', set_referrer = %d, `date_created` = '%s'",
			mysql_real_escape_string($shortcut),
			mysql_real_escape_string($destination),
			mysql_real_escape_string($set_referrer),
			date('Y-m-d H:i:s')
		);
		$result = mysql_query($query);
		return $result;
	}

	/**
	 * Get statistics for a specific short URL
	 * @param string $shortcut A shortcut that exists in the database
	 * @return array An array of data about the shortcut usage
	 */
	function get_shortcut($shortcut){
		$data = array();
		$query = sprintf(
			"SELECT `short_url`.*, count(`short_url_tracking`.`shortcut_id`) as `hits`
			FROM `short_url`
				LEFT JOIN `short_url_tracking` USING(`shortcut_id`)
			WHERE `shortcut` = '%s' 
			GROUP BY `shortcut_id`",
			mysql_real_escape_string($shortcut)
		);
		$result = mysql_query($query);
		$data = mysql_fetch_array($result, MYSQL_ASSOC);
		return $data;
	}

	/**
	 * Get information about shortcuts
	 * @return array An array of data about the shortcuts
	 */
	function get_shortcuts(){
		$result = mysql_query(
			"SELECT `short_url`.*, count(`short_url_tracking`.`shortcut_id`) as `hits`
			FROM `short_url`
				LEFT JOIN `short_url_tracking` USING(`shortcut_id`)
			GROUP BY `shortcut_id`
			ORDER BY `shortcut_id` ASC"
		);
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Save a shortcut request
	 * @param string $shortcut A shortcut key/code
	 */
	function track_shortcut($shortcut_id, $ip_address, $source){
		$query = sprintf(
			"INSERT INTO `short_url_tracking` SET
				`shortcut_id` = %d, `request_time` = '%s', `ip_address` = INET_ATON('%s'),
				`source` = '%s'",
			$shortcut_id, date('Y-m-d H:i:s'), $ip_address,
			mysql_real_escape_string($source)
		);
		mysql_query($query);
	}

} // END class

?>