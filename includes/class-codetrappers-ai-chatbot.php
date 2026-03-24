<?php
namespace Codetrappers\CodetrappersAiChatbot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CodetrappersAiChatbotPlugin {
	const OPTION_KEY = 'codetrappers-ai-chatbot_settings';

	public function boot() {
		add_action( 'init', array( $this, 'register_post_meta' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
        add_action( 'admin_menu', array( $this, 'register_ai_page' ) );
        add_action( 'admin_post_codetrappers_ai_chatbot_generate', array( $this, 'handle_generation' ) );
	}

	public function register_post_meta() {
		register_post_meta(
			'',
			'_codetrappers-ai-chatbot_status',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	public function register_settings() {
		register_setting(
			'general',
			'codetrappers-ai-chatbot_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'provider' => 'mock',
					'model'    => 'starter-model',
				),
			)
		);
		register_setting(
			'general',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'enabled' => true,
					'notes'   => 'ai, chatbot, shortcode',
				),
			)
		);
	}

	public function sanitize_settings( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();

		return array(
			'enabled' => ! empty( $settings['enabled'] ),
			'notes'   => isset( $settings['notes'] ) ? sanitize_text_field( $settings['notes'] ) : '',
			'provider' => isset( $settings['provider'] ) ? sanitize_key( $settings['provider'] ) : 'mock',
			'model'    => isset( $settings['model'] ) ? sanitize_text_field( $settings['model'] ) : 'starter-model',
		);
	}

	public function render_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'settings_page_codetrappers-ai-chatbot' === $screen->id ) {
			return;
		}

		$settings = get_option( self::OPTION_KEY, array() );

		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p>%s</p></div>',
			esc_html__( 'Codetrappers AI Chatbot starter is active. Extend the bootstrap logic in includes/class-codetrappers-ai-chatbot.php.', 'codetrappers-ai-chatbot' )
		);
	}

    public function register_ai_page() {
        add_submenu_page(
            'options-general.php',
            __( 'Codetrappers AI Chatbot', 'codetrappers-ai-chatbot' ),
            __( 'Codetrappers AI Chatbot', 'codetrappers-ai-chatbot' ),
            'manage_options',
            'codetrappers-ai-chatbot',
            array( $this, 'render_ai_page' )
        );
    }

    public function render_ai_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $output = get_transient( 'codetrappers-ai-chatbot_last_output' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Codetrappers AI Chatbot', 'codetrappers-ai-chatbot' ); ?></h1>
            <p><?php echo esc_html__( 'This starter page wires WordPress admin UI to a replaceable AI provider.', 'codetrappers-ai-chatbot' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'codetrappers-ai-chatbot_generate' ); ?>
                <input type="hidden" name="action" value="codetrappers_ai_chatbot_generate" />
                <textarea name="prompt" class="large-text code" rows="8" placeholder="<?php echo esc_attr__( 'Enter a prompt or source content.', 'codetrappers-ai-chatbot' ); ?>"></textarea>
                <p><button type="submit" class="button button-primary"><?php echo esc_html__( 'Generate', 'codetrappers-ai-chatbot' ); ?></button></p>
            </form>
            <?php if ( ! empty( $output ) ) : ?>
                <h2><?php echo esc_html__( 'Latest Output', 'codetrappers-ai-chatbot' ); ?></h2>
                <pre style="white-space: pre-wrap;"><?php echo esc_html( $output ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }

    public function handle_generation() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'codetrappers-ai-chatbot' ) );
        }

        check_admin_referer( 'codetrappers-ai-chatbot_generate' );

        $prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $result = $this->generate_placeholder_response( $prompt );

        set_transient( 'codetrappers-ai-chatbot_last_output', $result, HOUR_IN_SECONDS );

        wp_safe_redirect( admin_url( 'options-general.php?page=codetrappers-ai-chatbot' ) );
        exit;
    }

    private function generate_placeholder_response( $prompt ) {
        $trimmed_prompt = trim( (string) $prompt );

        if ( '' === $trimmed_prompt ) {
            return __( 'No prompt was provided. Replace this placeholder with an actual AI provider call.', 'codetrappers-ai-chatbot' );
        }

        return sprintf(
            __( 'Placeholder response for: %s', 'codetrappers-ai-chatbot' ),
            $trimmed_prompt
        );
    }

}
