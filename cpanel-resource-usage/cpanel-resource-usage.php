<?php


function cpanel_resource_usage_widgets() {
   wp_add_dashboard_widget(
       'cpanel_resource_usage',
       'cPanel Server Resource Usage',
       'cpanel_resource_usage_function'
   );
}
add_action('wp_dashboard_setup', 'cpanel_resource_usage_widgets' );

function cpanel_resource_usage_settings_link($links) {
    $settings_link = '<a href="admin.php?page=render_settings_page">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cpanel_resource_usage_settings_link');

function get_server_information() {
    $server = get_option('hostname');
    $cpanel_user = get_option('username');
    $api_token = get_option('api_key');
    $url = "https://$server:2083/execute/ResourceUsage/get_usages";
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            "Authorization: cpanel $cpanel_user:" . preg_replace(['/%/', '/\n/'], ['%25', '%0A'], $api_token),
            "Content-Type: application/json"
        )
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function cpanel_resource_usage_function() { 
    wp_enqueue_style( 'tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.17/dist/tailwind.min.css' ); 
    
    $server_info = json_decode(get_server_information(), true);
    // echo '<pre>';
    // print_r($server_info);
    // echo '</pre>';
    
    foreach ($server_info['data'] as $item) {
        if ($item['id'] == 'disk_usage') {
          $disk_total = $item['maximum'];
          $disk_usage = $item['usage'];
          $disk_percentage = round(($disk_usage / $disk_total) * 100, 2);
          $disk_total = round($disk_total / (1024 * 1024 * 1024), 2) . ' GB';
          
          if ($disk_usage >= 1024 * 1024 * 1024) {
             
              // Convert bytes to GB
              $disk_usage = round($disk_usage / (1024 * 1024 * 1024), 2) . ' GB';
          } elseif ($disk_usage >= 1024 * 1024) {
              
              // Convert bytes to MB
              $disk_usage = round($disk_usage / (1024 * 1024), 2) . ' MB';
          } else {
              
              // Display in bytes
              $disk_usage = $disk_usage . ' bytes';
          }
               
       }
        if ($item['id'] == 'lvenproc') {
          $process_total = $item['maximum'];
          $process_usage = $item['usage'];
        }
        if ($item['id'] == 'lveep') {
          $entry_total = $item['maximum'];
          $entry_usage = $item['usage'];
          $entry_percentage = round(($entry_usage / $entry_total) * 100, 2);
        }
        if ($item['id'] == 'lvecpu') {
          $cpu_total = $item['maximum'];
          $cpu_usage = $item['usage'];
        }
        
        if ($item['id'] == 'lvememphy') {
          $mem_total = $item['maximum'];
          $mem_usage = $item['usage'];
          $mem_percentage = round(($mem_usage / $mem_total) * 100, 2);
          $mem_total = round($mem_total / (1024 * 1024 * 1024), 2) . ' GB';

          if ($mem_usage >= 1024 * 1024 * 1024) {
             
              // Convert bytes to GB
              $mem_usage = round($mem_usage / (1024 * 1024 * 1024), 2) . ' GB';
          } elseif ($mem_usage >= 1024 * 1024) {
              
              // Convert bytes to MB
              $mem_usage = round($mem_usage / (1024 * 1024), 2) . ' MB';
          } else {
              
              // Display in bytes
              $mem_usage = $mem_usage . ' bytes';
          }
               
          // if ($mem_total == 1024 * 1024 * 1024) {
          //     $mem_total = '1 GB';
          // } elseif ($mem_total >= 1024 * 1024) {
          //     // Convert bytes to MB
          //     $mem_total = round($mem_total / (1024 * 1024), 2) . ' MB';
          // } else {
          //     // Display in bytes
          //     $mem_total = $mem_total . ' bytes';
          // }

        }
    }
    ?>
    
    <section class="py-2">
      <div class="flex justify-between">
        <span class="font-bold font-slate-800 mb-2">Memory usage:</span>
        <span class="font-slate-200 font-xs"><?=$mem_usage?> of <?=$mem_total?></span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="<?= $mem_percentage >= 80 ? 'bg-red-400' : ($mem_percentage >= 60 ? 'bg-yellow-400' : 'bg-blue-400') ?> h-2.5 rounded-full" style="width: <?= $mem_percentage ?>%"></div>
      </div>
    </section>
    <section class="py-2">
      <div class="flex justify-between">
        <span class="font-bold font-slate-800 mb-2">CPU usage:</span>
        <span class="font-slate-200 font-xs"><?=$cpu_usage?>%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="<?= $cpu_usage >= 80 ? 'bg-red-400' : ($cpu_usage >= 60 ? 'bg-yellow-400' : 'bg-blue-400') ?> h-2.5 rounded-full" style="width: <?= $cpu_usage ?>%"></div>
      </div>
    </section>
    <section class="py-2">
      <div class="flex justify-between">
        <span class="font-bold font-slate-800 mb-2">Disk usage:</span>
        <span class="font-slate-200 font-xs"><?=$disk_usage?> of <?=$disk_total?></span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="<?= $disk_percentage >= 80 ? 'bg-red-400' : ($disk_percentage >= 60 ? 'bg-yellow-400' : 'bg-blue-400') ?> h-2.5 rounded-full" style="width: <?= $disk_percentage ?>%"></div>
      </div>
    </section>
    <section class="py-2">
      <div class="flex justify-between">
        <span class="font-bold font-slate-800 mb-2">Number of processes:</span>
        <span class="font-slate-200 font-xs"><?=$process_usage?> of <?=$process_total?></span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="bg-blue-400 h-2.5 rounded-full" style="width: <?=$process_usage?>%"></div>
      </div>
    </section>
    <section class="py-2">
      <div class="flex justify-between">
        <span class="font-bold font-slate-800 mb-2">Entry processes:</span>
        <span class="font-slate-200 font-xs"><?=$entry_usage?> of <?=$entry_total?></span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="bg-blue-400 h-2.5 rounded-full" style="width: <?=$entry_percentage?>%"></div>
      </div>
    </section>
    <?php 
}

// Add a new menu item under "Settings"
add_action('admin_menu', 'add_settings_page');
function add_settings_page() {
    add_options_page('cPanel Resource Usage Settings', 'cPanel Resource Usage', 'manage_options', 'cpanel-resource-usage-settings', 'render_settings_page');
}

// Create the settings page HTML
function render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('settings_group'); ?>
            <?php do_settings_sections('cpanel-resource-usage-settings'); ?>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// Register the settings and fields
add_action('admin_init', 'register_settings');
function register_settings() {
    register_setting('settings_group', 'api_key');
    register_setting('settings_group', 'username');
    register_setting('settings_group', 'hostname');
    
    add_settings_section('general_section', '', 'render_general_section', 'cpanel-resource-usage-settings');
    
    add_settings_field('api_key_field', 'API Key', 'render_api_key_field', 'cpanel-resource-usage-settings', 'general_section');
    add_settings_field('username_field', 'Username', 'render_username_field', 'cpanel-resource-usage-settings', 'general_section');
    add_settings_field('hostname_field', 'Hostname', 'render_hostname_field', 'cpanel-resource-usage-settings', 'general_section');
}

// Render the settings fields
function render_general_section() {
    // Add any introductory text here
}

function render_api_key_field() {
    $api_key = get_option('api_key');
    ?>
    <input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" />
    <p class="description">Enter your API key here.</p>
    <?php
}

function render_username_field() {
    $username = get_option('username');
    ?>
    <input type="text" name="username" value="<?php echo esc_attr($username); ?>" />
    <p class="description">Enter your cPanel username here.</p>
    <?php
}

function render_hostname_field() {
    $hostname = get_option('hostname');
    ?>
    <input type="text" name="hostname" value="<?php echo esc_attr($hostname); ?>" />
    <p class="description">Enter your cPanel url without the https://</p>
    <?php
}
