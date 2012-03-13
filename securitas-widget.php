<?php

class SecuritasWidget extends WP_Widget {

  function __construct() {
    parent::__construct('baseID', 'name');
    $widget_ops = array('classname' => 'SecuritasWidget', 'description' => 'Displays the Securitas contact information');
    $this->WP_Widget('SecuritasWidget', 'Securitas contact person', $widget_ops);
  }

  function form($instance) {
    $instance = wp_parse_args((array) $instance, array('title' => ''));
    $title = $instance['title'];
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }

  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    if (is_user_logged_in()) {
      echo $before_widget;
      echo $before_title . __('Your contact with Securitas', 'securitas-ws') . $after_title;

      //the actual code   
      $sws = new SecuritasWS();
      $data = $sws->getSecuritasContact();
      echo $data['name'] . ' ' . $data['phone'] . '</br>';
      echo '<a href="mailto:' . $data['email'] . '" > ' . $data['email'] . '</a>';

      echo '' . $after_widget;
    }
  }

}