<?php
/*!
 * DiscordNotifier for Anodyne Nova 2
 *
 * An extension that notifies a Discord channel when mission posts
 * are published.
 *
 * Please make sure to provide settings, especially the
 * Discord hook URL. Go to "Settings" -> "User-created settings"
 * and provide the correct hook URL to "Discord Mod: Hook URL"
 *
 */
require( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

// Saving for the first time
$this->ci->event->listen(
	['db', 'insert', 'prepare', 'posts', 'write', 'missionpost'],
	function ( $event ) {
		$process = new \DiscordNotifier\Process( $this->ci );
		$process->processEvent( $event );
	}
);

// Updating a post
$this->ci->event->listen(
	['db', 'update', 'prepare', 'posts', 'write', 'missionpost'],
	function ( $event ) {
		$process = new \DiscordNotifier\Process( $this->ci );
		$process->processEvent( $event );
	}
);
