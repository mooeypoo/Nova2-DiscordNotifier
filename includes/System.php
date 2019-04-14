<?php
namespace DiscordNotifier;

class System {
	protected $settingsKeyPrefix = 'discord_hook_mod_';

	function __construct( $settingsModel ) {
		$this->settings = $settingsModel;
		$this->definitions = [
			[
 				'key' => 'hook_url',
 				'label' => 'Discord Mod: Hook URL',
 				'default' => ''
 			],
 			[
 				'key' => 'ellipses',
 				'label' => 'Discord Mod: A "read more" indicator for the end of the text snippet.',
 				'default' => '[ ... ]'
 			],
 			[
 				'key' => 'snippet_length',
 				'label' => 'Discord Mod: How many characters should the snippet show. Maximum 2048 characters. 0 for not displaying a snippet at all.',
 				'default' => 256
 			],
 			[
 				'key' => 'intro',
 				'label' => 'Discord Mod: An introduction text for the post. Leave empty for no intro.',
 				'default' => 'A new post was published!'
 			],
 			[
 				'key' => 'footer_text',
 				'label' => 'Discord Mod: A short text for the message footer. Leave empty for no footer.',
 				'default' => ''
 			],
 			[
 				'key' => 'footer_icon',
 				'label' => 'Discord Mod: A valid URL for an icon for the footer.',
 				'default' => ''
 			],
 			[
 				'key' => 'sidebar_color',
 				'label' => 'Discord Mod: The color used for the message sidebar. Leave empty for the default grey.',
 				'default' => ''
 			],
		];
		$this->values = [];
	}

	/**
	 * Make sure all setting keys are installed.
	 * It skips keys that are already installed,
	 * so it is a no-op if there are no new keys
	 * or if the system is alreayd installed.
	 */
	public function install() {
		// Set up necessary settings, if they don't exist yet
 		foreach ( $this->definitions as $definition ) {
 			$setting = $this->settings->get_setting( $definition[ 'key' ] );
 			if ( $setting === false ) {
 				// Add this setting
 				$this->settings->add_new_setting( [
 					'setting_key' => $this->$settingsKeyPrefix . $definition[ 'key' ],
 					'setting_label' => $definition[ 'label' ],
 					'setting_value' => $definition[ 'default' ],
 				] );
				$value = $definition[ 'default' ];
 			} else {
				$value = $setting;
			}

			$this->values[ $definition[ 'key' ] ] = $value;
 		}
	}

	public function getSettings() {
		return $this->values;
	}
}
