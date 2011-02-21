<?php

/**
 * Unfuddle Repository Callback XMPP Notify
 *
 * @link http://unfuddle.com/docs/topics/repository_callbacks
 * @author 2BJ dev2bj+gmail
 */

error_reporting(E_ALL ^ E_WARNING);
set_time_limit(0);
date_default_timezone_get('Europe/Moscow');

// Unfuddle config
define('UNFUDDLE_SUBDOMAIN', '<subdomain>');
// recipients
$developers = array('user1@domain.tld', 'user2@domain.tld', 'user3@domain.tld');

// XMPP config
define('XMPP_HOST', 'talk.google.com');
define('XMPP_PORT', 5222);
define('XMPP_USER', '<bot>');
define('XMPP_PASSWD', '<password>');
define('XMPP_RESOURCE', 'xmpphp');
define('XMPP_SERVER', 'talk.google.com'); // or you'r Google Apps for Your Domain
// Run..
$commit = (array) simplexml_load_string(file_get_contents('php://input'));

if ( ! isset($committ['author-date']))
{
    exit;
}

$message = sprintf(
    "Commit from %s (%s):" . PHP_EOL . "----------" . PHP_EOL . "%s" . PHP_EOL . "----------" . PHP_EOL . "URL: "
    . sprintf(
        "http://%s.unfuddle.com/a#/repositories/%d/commit?commit=%s",
        UNFUDDLE_SUBDOMAIN,
        $commit['repository-id'],
        $commit['revision']
    ),
    $commit['committer-name'],
    date('d.m.Y H:i', strtotime($commit['author-date'])),
    $commit['message']
);

include 'XMPPHP/XMPP.php';

$conn = new XMPPHP_XMPP(XMPP_HOST, XMPP_PORT, XMPP_USER, XMPP_PASSWD, XMPP_RESOURCE, XMPP_SERVER);

try
{
    $conn->connect();
    $conn->processUntil('session_start');
    $conn->presence();
    foreach ($developers as $developer)
    {
        $conn->message($developer, $message);
    }
    $conn->disconnect();
}
catch (XMPPHP_Exception $e)
{
    die($e->getMessage());
}
