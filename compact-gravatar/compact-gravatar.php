<?php
/**
Plugin Name: Compact Gravatar Widget
Plugin URI: https://github.com/ChrTang/wordpress-compact-gravatar
Description: Widget that displays the Gravatar for a specific user including name and location.
Version: 1.0
Author: Christian Tang
Author URI: http://tangc.dk
**/

class compact_gravatar_widget extends WP_Widget { 
    /** constructor -- name this the same as the class above */
    function compact_gravatar_widget() {
        parent::WP_Widget(false, $name = 'Compact Gravatar');	
    }
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {	
        extract( $args );
        $title 	   = apply_filters('widget_title', $instance['title']);
        $link_text = $instance['link_text'];
        $user 	   = $instance['user'];
        $size 	   = $instance['size'];
        $default   = $instance['default'];
        $hide_name = $instance['hide_name'];
        $hide_loc  = $instance['hide_loc'];
        $hide_link = $instance['hide_link'];
        
        $user_info = get_userdata($user); // Get User Info from WordPress (e-mail)
        
        // Get profile as JSON from gravatar.com
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,'http://en.gravatar.com/'.md5($user_info->user_email).'.json');
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        $result = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($result);
        
        $name     = $json->entry[0]->displayName;
        $location = $json->entry[0]->currentLocation;
        $url      = $json->entry[0]->profileUrl;
        
        // Build URL for avatar
        if($size == null || $size == '')
            $size = 50;
        $avatarUrl = 'http://www.gravatar.com/avatar/'.md5($user_info->user_email).'?size='.$size; 
        $wp_avatar = get_option('avatar_default');
        if($default == 'wp')
            $avatarUrl .= '&amp;d='.($wp_avatar == 'Mystery Man' ? 'mm' : $wp_avatar);
        else
            $avatarUrl .= '&amp;d='.$default;
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                            <style type="text/css">
                            .compact-gravatar {
                                display: block;
                            }
                            .gravatar-texts {
                                vertical-align: top;
                            }
                            .compact-gravatar img {
                                border: 1px solid #000000;
                                float: left;
                                margin: 3px;
                                margin-top: 2px;
                            }
                            </style>
                            <div class="compact-gravatar">
                            <img src="<?php echo $avatarUrl; ?>" />
                            <div class="gravatar-texts">
                            <?php if(!$hide_name) { ?>
                            <strong class="gravatar-name"><?php echo $name; ?></strong><br />
                            <?php } ?>
                            <?php if(!$hide_loc) { ?>
                            <span class="gravatar-loc"><?php echo $location; ?></span><br />
                            <?php } ?>
                            <?php if(!$hide_link) { ?>
                            <a class="gravatar-link" href="<?php echo $url; ?>"><?php echo $link_text; ?> &gt;&gt;</a>
                            <?php } ?>
                            </div>
                            </div>
              <?php echo $after_widget; ?>
        <?php
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title']     = strip_tags($new_instance['title']);
        $instance['link_text'] = strip_tags($new_instance['link_text']);
		$instance['user']      = $new_instance['user'];
        $instance['size']      = $new_instance['size'];
        $instance['default']   = $new_instance['default'];
        $instance['hide_name'] = $new_instance['hide_name'];
        $instance['hide_loc']  = $new_instance['hide_loc'];
        $instance['hide_link'] = $new_instance['hide_link'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {	
 
        $title 	   = esc_attr($instance['title']);
        $link_text = esc_attr($instance['link_text']);
        $user      = esc_attr($instance['user']);
        $size      = esc_attr($instance['size']);
        $default   = esc_attr($instance['default']);
        $hide_name = esc_attr($instance['hide_name']);
        $hide_loc  = esc_attr($instance['hide_loc']);
        $hide_link = esc_attr($instance['hide_link']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'compact-gravatar'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('link_text'); ?>"><?php _e('Profile Link Text:', 'compact-gravatar'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('link_text'); ?>" name="<?php echo $this->get_field_name('link_text'); ?>" type="text" value="<?php echo $link_text; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('User:', 'compact-gravatar'); ?></label> 
          <?php wp_dropdown_users(array('name' => $this->get_field_name('user'), 'selected'=>$user, 'show_option_none'=>__('Select User', 'compact-gravatar'))); ?> 
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Image Size:', 'compact-gravatar'); ?></label>
          <input id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" type="text" value="<?php echo $size; ?>" style="width: 50px;" /> (<?php _e('Default', 'compact-gravatar'); ?>: 50)
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('default'); ?>"><?php _e('Default Image:', 'compact-gravatar'); ?></label> 
            <select id="<?php echo $this->get_field_id('default'); ?>" name="<?php echo $this->get_field_name('default'); ?>">
                <option value="wp"<?php echo ('wp' ? ' selected="selected"' : ''); ?>><?php _e('Use WordPress Settings', 'compact-gravatar'); ?></option>
                <option value=""<?php echo ($default == '' || $default == null ? ' selected="selected"' : ''); ?>><?php _e('Use default from Gravatar', 'compact-gravatar'); ?></option>
                <option value="mm"<?php echo ($default == 'mm' ? ' selected="selected"' : ''); ?>>Mystery-Man</option>
                <option value="identicon"<?php echo ($default == 'identicon' ? ' selected="selected"' : ''); ?>>Identicon</option>
                <option value="monsterid"<?php echo ($default == 'monsterid' ? ' selected="selected"' : ''); ?>>Monster</option>
                <option value="wavatar"<?php echo ($default == 'wavatar' ? ' selected="selected"' : ''); ?>>Wavatar</option>
                <option value="retro"<?php echo ($default == 'retro' ? ' selected="selected"' : ''); ?>>Retro</option>
                <option value="blank"<?php echo ($default == 'blank' ? ' selected="selected"' : ''); ?>><?php _e('Blank', 'compact-gravatar'); ?></option>
                <option value="404"<?php echo ($default == '404' ? ' selected="selected"' : ''); ?>><?php _e('Return HTTP 404 (File Not Found)', 'compact-gravatar'); ?></option>
            </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('hide_name'); ?>" name="<?php echo $this->get_field_name('hide_name'); ?>" value="true"<?php echo ($hide_name == true ? ' checked="checked"' : ''); ?> />
            <label for="<?php echo $this->get_field_id('hide_name'); ?>"><?php _e("Hide name", 'compact-gravatar'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('hide_loc'); ?>" name="<?php echo $this->get_field_name('hide_loc'); ?>" value="true"<?php echo ($hide_loc == true ? ' checked="checked"' : ''); ?> />
            <label for="<?php echo $this->get_field_id('hide_loc'); ?>"><?php _e("Hide location", 'compact-gravatar'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('hide_link'); ?>" name="<?php echo $this->get_field_name('hide_link'); ?>" value="true"<?php echo ($hide_link == true ? ' checked="checked"' : ''); ?> />
            <label for="<?php echo $this->get_field_id('hide_link'); ?>"><?php _e("Hide link", 'compact-gravatar'); ?></label>
        </p>
        <?php 
    }
 
 
} // end class compact_gravatar_widget

function compact_gravatar_init() {
    // Register widget
    register_widget( 'compact_gravatar_widget');
}
add_action( 'widgets_init', 'compact_gravatar_init' );