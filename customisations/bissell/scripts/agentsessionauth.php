<?php

// Find our position in the file tree
if (!defined('DOCROOT')) {
    $docroot = get_cfg_var('doc_root');
    define('DOCROOT', $docroot);
}

/************* Agent Authentication ***************/

// Set up and call the AgentAuthenticator
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');

// On failure, this includes the Access Denied page and then exits,
// preventing the rest of the page from running.

// The sid is passed to all scripts so that authentication can be checked in all php scripts.
$account = AgentAuthenticator::authenticateSessionID($_GET['sid']);

?>