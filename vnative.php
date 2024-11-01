<?php
/**
 * 
 *Plugin Name: vNative
 *Description: Empowering smart performance marketing, through native sponsored content unit.
 *Version: 2.5
 *Author: vNative
 *Author URI: http://vnative.com
*/

require 'autoloader.php';

$dir = plugin_dir_url( __FILE__ );

$p_id = get_option('vpublisher_id');

$api = get_option('vapi_key');

    if(empty($p_id)){

      $p_id = '';
    }

function vnative_footer_function() {

  global $p_id;
    
    echo "
<!--VNATIVE PLUGIN-->

<script>(function (we, a, r, e, vnative){we['vNativeObject']=vnative;we[vnative]=we[vnative]||function(){(i[vnative].q=i[r].q || []).push(arguments)};var x,y;x=a.createElement(r),y=a.getElementsByTagName(r)[0];x.async=true;x.src=e;y.parentNode.insertBefore(x, y);}(window,document,'script','//serve.vnative.com/js/native.js','vn'));
</script>
<ins class='byvnative'
data-client='pub-" . esc_html($p_id) . "'
data-format='all'></ins>

<!--VNATIVE PLUGIN-->";

}

if(!empty($p_id)){
  add_action( 'wp_footer', 'vnative_footer_function' );
}
 
add_option('vpublisher_id');
add_option('vapi_key');

add_action('admin_init', 'vnative_register_my_setting');

function vnative_register_my_setting(){

  register_setting('vpublisher_options', 'vpublisher_id');
  register_setting('v2publisher_options', 'vapi_key');
}

function vnative_custom_admin_menu() {
    add_options_page(
        'vnative',
        'vnative',
        'manage_options',
        'vnative',
        'vnative_options_page'
    );
}

function vnative_options_page() {

  global $p_id, $dir, $api;

  $curl = new Curl\Curl;
  $curl->setHeader('X-Api-Key', $api);
  $response = $curl->get('https://api.vnative.com/affiliate/report?start=2017-01-01');
  $clicks = [];
  $convs = [];
  $imps = [];
  $revenue = [];
  $dates = [];
  if(isset($response->data->performance)){
      foreach ($response->data->performance as $key => $value) {
        $clicks[] = $value->clicks;
        $convs[] = $value->conversions;
        $imps[] = $value->impressions;
        $revenue[] = $value->revenue;
        $dates[] = $value->created;
      }
    }else{
        $error="Not Valid Api Key";
    }
  $curl->close();
    ?>
    <h2>vNative</h2>
    <!-- Latest compiled and minified CSS -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript" src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.js'></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <div class="wrap container-fluid">
        <div>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#home" aria-controls="home" role="tab" data-toggle="tab">Home</a>
                </li>
                <li role="presentation">
                    <a href="#stats" aria-controls="stats" role="tab" data-toggle="tab">Stats</a>
                </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="home">
                    <form action="options.php" method="post">
                        <?php settings_fields('vpublisher_options')?>
                        <p style="font-size: 14px;">Please copy the Publisher ID and Paste here.</p>
                        Publisher ID: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="text" name="vpublisher_id" value="<?php echo $p_id;?>" placeholder='Enter Publisher ID' style="width:250px" maxlength='100'>
                        <br>
                        <br>
                        <br>
                        <input type="submit" class="button-primary" name="save_publisher_id" value="save">
                    </form>
                    <img src=<?php echo $dir . 'sample.png';?> style="width:50%; height:40%; margin-left:500px; margin-top:-100px">
                </div>
                <div role="tabpanel" class="tab-pane" id="stats">
                    <form action="options.php" method="post">
                        <?php if (!empty($api)) {
                            if (isset($error)) {
                             echo '<br><br><span style="color:red">' . $error . '</span>';
                            }?>
                        <div style="width:75%;">
                            <canvas id="canvas"></canvas>
                        </div>
                        <br>
                        <br>
                        <script>
                            var config = {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($dates);?>,
                                    datasets: [{
                                        label: "Clicks",
                                        data: <?php echo json_encode($clicks);?>,
                                        fill: false,
                                    }, {
                                        label: "Conversions",
                                        data: <?php echo json_encode($convs);?>,
                                        fill: false,
                                    }, {
                                        label: "Impressions",
                                        data: <?php echo json_encode($imps);?>,
                                        lineTension: 0,
                                        fill: false,
                                    }, {
                                        label: "Revenue",
                                        data: <?php echo json_encode($revenue);?>,
                                        fill: false,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'bottom',
                                    },
                                    hover: {
                                        mode: 'label'
                                    },
                                    scales: {
                                        xAxes: [{
                                            display: true,
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Month'
                                            }
                                        }],
                                        yAxes: [{
                                            display: true,
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Value'
                                            }
                                        }]
                                    },
                                    title: {
                                        display: true,
                                        text: 'Chart.js Line Chart - Legend'
                                    }
                                }
                            };

                            var color = ['red', 'blue', 'green', 'yellow'];
                            var j=0;

                            $.each(config.data.datasets, function(i, dataset) {
                                var background = color[j];
                                dataset.borderColor = background;
                                dataset.backgroundColor = background;
                                dataset.pointBorderColor = background;
                                dataset.pointBackgroundColor = background;
                                dataset.pointBorderWidth = 1;
                                j++;
                            });

                            window.onload = function() {
                                var ctx = document.getElementById("canvas").getContext("2d");
                                window.myLine = new Chart(ctx, config);
                            };
                        </script>
                        <?php }?>
                        <?php settings_fields('v2publisher_options')?>
                        <p style="font-size: 14px;"></p>
                        Api Key: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="text" name="vapi_key" value="<?php echo $api;?>" placeholder='Enter Api Key' style="width:250px" maxlength='100'>
                        <br><br>
                        <input type="submit" class="button-primary" name="save_api_key" value="save">
                    </form>
                </div>
            </div>
        </div>
        <?php
}
add_action( 'admin_menu', 'vnative_custom_admin_menu' );

function vnative_plugin_row_meta( $links, $file ) {

    if (strpos( $file,'vnative.php') !== false ) {
        $new_links = array('<a href="mailto:info@vnative.com">Support</a>');
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

add_filter('plugin_row_meta', 'vnative_plugin_row_meta', 10, 2 );

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'vnative_add_action_links' );

function vnative_add_action_links ( $links ) {
 $mylinks = array(
 '<a href="options-general.php?page=vnative">Settings</a>',
 '<a href="https://www.facebook.com/officialvnative" target="_blank" class="dashicons-before dashicons-facebook-alt"></a>',
 '<a href="https://plus.google.com/+Vnative" target="_blank" class="dashicons-before dashicons-googleplus"></a>',
 '<a href="https://twitter.com/officialvnative" target="_blank" class="dashicons-before dashicons-twitter"></a>',
 );
return array_merge( $links, $mylinks );
}
