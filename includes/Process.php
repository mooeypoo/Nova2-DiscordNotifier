<?php
namespace DiscordNotifier;

class Process {
	protected $ci;
	protected $system;
	protected $settings;
	protected $discordHookURL;

	public function __construct( $ci ) {
		$this->ci = $ci;
		$this->ci->load->model('settings_model', 'settings');
		$this->ci->load->model('characters_model', 'char');
		$this->ci->load->model('missions_model', 'mis');

		$this->system = new System( $this->ci->settings );
		$this->system->install();
		$this->settings = $this->system->getSettings();

		$this->discordHookURL = $this->settings['hook_url'];
	}

	public function processEvent( $event ) {
		if (
			$event['data']['post_status'] !== 'activated' ||
			!$this->discordHookURL
		) {
			return;
		}

		$data = [
			'status' => $event['data']['post_status'],
			'char_ids' => $event['data']['post_authors'],
			// 'user_ids' => $event['data']['post_authors_users'],
			'date' => $event['data']['post_date'],
			'title' => $event['data']['post_title'],
			'content' => $event['data']['post_content'],
			// 'mission' => $this->ci->mis->get_mission($event['data']['post_mission'], 'mission_title'),
			'mission_id' => $event['data']['post_mission'],
			'location' => $event['data']['post_location'],
		];

		// Get authors names array
		$charIDs = explode( ',', $data['char_ids'] );
		$chars = [];
		foreach ( $charIDs as $cid ) {
			$chars[] = $this->ci->char->get_character_name(
				$cid,
				// TODO: Allow for settings for these
				false, // Show rank
				false, // Show short rank
				false
			);
		}

		$data['chars'] = $chars;

		$embed = $this->createEmbed( $data );

		$this->sendToDiscord( $embed );
	}

	protected function sendToDiscord( $embed ) {
		$hookData = [
			'content' => $this->settings['mod_intro'],
			'tts' => false,
			'embeds' => [ $embed ],
		];

		// Send post
 		$ch = curl_init( $hookURL );

 		curl_setopt_array($ch, array(
 			CURLOPT_POST => TRUE,
 			CURLOPT_RETURNTRANSFER => TRUE,
 			CURLOPT_HTTPHEADER => array(
 				'Content-Type: application/json'
 			),
 			CURLOPT_POSTFIELDS => json_encode( $hookData )
 		));

 		// Send the request
 		$response = curl_exec($ch);
		curl_close( $ch );
	}

	/**
	 * Create the embed piece that's expected when using Discord message API
	 *
	 * @param array $info Required information for the embed.
	 *  Keys in the info paramter:
	 *  - title (string) Post title
	 *  - chars (string[]) An array of character names
	 *  - mission (string) Mission name
	 *  - content (string) Content of the post
	 * @return array An object representing the 'embed' portion
	 *  that's expected in Discord's message API
	 */
	protected function createEmbed( $info ) {
		$embed = [
			'title' => $info['title'],
			// TODO: Check into listening to event *after* db processing to get post id
			// 'url' => site_url( 'sim/viewpost/'. $postID ),
			'url' => site_url( 'sim/listposts/mission/' . $info['mission_id'] ),
			'color' => $this->settings['sidebar_color'],
			'fields' => [
				[
					'name' => 'Authors',
					'value' => join( $info['chars'], ', ' ),
				],
				// [
				// 	'name' => 'Mission',
				// 	'value' => $info['mission'],
				// ],
			],
		];

		if ( $this->settings['snippet_length'] > 0 ) {
			$embed[ 'description' ] = substr(
					$info['content'], 0,
					$this->settings['snippet_length']
				) .
				$this->settings['ellipses'];
		}

		if ( $this->settings[ 'footer_text' ] ) {
			$embed[ 'footer' ] = [
				'text' => $this->settings[ 'footer_text' ],
				'icon_url' => $this->settings[ 'footer_icon' ]
			];
		}

		return $embed;
	}

}
