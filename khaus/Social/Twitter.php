<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus_Social
 * @package     Khaus
 * @version     1:20130731
 */

class Khaus_Social_Twitter
{
	private $_conexion;
	private $_uriBase;

	public function __construct()
	{
		require_once('_twitteroauth/twitteroauth.php');
		$this->_uriBase = 'https://api.twitter.com/1.1';
	}

	public function login($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
	{
		$this->_conexion = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	}

	public function get($url)
	{
		return $this->_conexion->get($url);
	}

	public function getLatestTweets($username, $quantity)
	{
		$tweets = array();
		$url = sprintf('%s/statuses/user_timeline.json?screen_name=%s&count=%d', $this->_uriBase, $username, $quantity);
		foreach ($this->get($url) as $key => $value) {
			$tweetData = new stdClass;
			$tweetData->text = $value->text;
			$tweetData->datetime = $value->created_at;
			$tweets[$key] = $tweetData;
		}
		return $tweets;
	}
}