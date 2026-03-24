<?php
/**
 * Plugin Name: Coetrappers AI Chatbot
 * Description: Starter AI chatbot plugin with a basic shortcode and provider abstraction.
 * Version: 0.1.0
 * Author: Coetrappers
 * License: GPL-2.0-or-later
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: coetrappers-ai-chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COETRAPPERS_AI_CHATBOT_VERSION', '0.1.0' );
define( 'COETRAPPERS_AI_CHATBOT_FILE', __FILE__ );
define( 'COETRAPPERS_AI_CHATBOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'COETRAPPERS_AI_CHATBOT_URL', plugin_dir_url( __FILE__ ) );

require_once COETRAPPERS_AI_CHATBOT_PATH . 'includes/class-coetrappers-ai-chatbot.php';

function coetrappers_ai_chatbot_bootstrap() {
	$plugin = new \Coetrappers\CoetrappersAiChatbot\CoetrappersAiChatbotPlugin();
	$plugin->boot();
}

coetrappers_ai_chatbot_bootstrap();
