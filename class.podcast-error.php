<?php

Class Podcastor_error {

    function __construct()
    {
        
    }
    
    static function get_error_message($message='') {
        if (empty($message)) $message= 'Error with podcastor generator. Please check shortcode and try again.'
        ?>
        <div class="podcastor-error-msg">
            <b><?php echo $message; ?></b>
        </div>

        <?php
    }
}