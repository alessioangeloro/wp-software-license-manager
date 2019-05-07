<?php

function wp_lic_mgr_add_licenses_menu() {
    global $wpdb;
    //initialise some variables
    $id = '';
    $license_key = '';
    $max_domains = 1;
    $license_status = '';
    $first_name = '';
    $last_name = '';
    $email = '';
    $company_name = '';
    $txn_id = '';
    $reset_count = '';
    $created_date = '';
    $renewed_date = '';
    $expiry_date = '';
    $current_date = (date ("Y-m-d"));
    $current_date_plus_1year = date('Y-m-d', strtotime('+1 year'));
    $product_ref = '';
    $subscr_id = '';

    $slm_options = get_option('slm_plugin_options');
    
    echo '<div class="wrap">';
    echo '<h2>Add/Edit Licenses</h2>';
    echo '<div id="poststuff"><div id="post-body">';

    //If product is being edited, grab current product info
    if (isset($_GET['edit_record'])) {
        $errors = '';
        $id = $_GET['edit_record'];
        $lk_table = SLM_TBL_LICENSE_KEYS;
        $sql_prep = $wpdb->prepare("SELECT * FROM $lk_table WHERE id = %s", $id);
        $record = $wpdb->get_row($sql_prep, OBJECT);
        $license_key = $record->license_key;
        $max_domains = $record->max_allowed_domains;
        $license_status = $record->lic_status;
        $first_name = $record->first_name;
        $last_name = $record->last_name;
        $email = $record->email;
        $company_name = $record->company_name;
        $txn_id = $record->txn_id;
        $reset_count = $record->manual_reset_count;
        $created_date = $record->date_created;
        $renewed_date = $record->date_renewed;
        $expiry_date = $record->date_expiry;
        $product_ref = $record->product_ref;
        $subscr_id = $record->subscr_id;
    }
    
    
    if (isset($_POST['save_record'])) {
        
        //Check nonce
        if ( !isset($_POST['slm_add_edit_nonce_val']) || !wp_verify_nonce($_POST['slm_add_edit_nonce_val'], 'slm_add_edit_nonce_action' )){
            //Nonce check failed.
            wp_die("Error! Nonce verification failed for license save action.");
        }
        
        do_action('slm_add_edit_interface_save_submission');
        
        //TODO - do some validation
        $license_key = $_POST['license_key'];
        $max_domains = $_POST['max_allowed_domains'];
        $license_status = $_POST['lic_status'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $company_name = $_POST['company_name'];
        $txn_id = $_POST['txn_id'];
        $reset_count = $_POST['manual_reset_count'];
        $created_date = $_POST['date_created'];
        $renewed_date = $_POST['date_renewed'];
        $expiry_date = $_POST['date_expiry'];
        $product_ref = $_POST['product_ref'];
        $subscr_id = $_POST['subscr_id'];
        
        if(empty($created_date)){
            $created_date = $current_date;
        }
        if(empty($renewed_date)){
            $renewed_date = $current_date;
        }
        if(empty($expiry_date)){
            $expiry_date = $current_date_plus_1year;
        }
        
        //Save the entry to the database
        $fields = array();
        $fields['license_key'] = $license_key;
        $fields['max_allowed_domains'] = $max_domains;
        $fields['lic_status'] = $license_status;
        $fields['first_name'] = $first_name;
        $fields['last_name'] = $last_name;
        $fields['email'] = $email;
        $fields['company_name'] = $company_name;
        $fields['txn_id'] = $txn_id;
        $fields['manual_reset_count'] = $reset_count;
        $fields['date_created'] = $created_date;
        $fields['date_renewed'] = $renewed_date;
        $fields['date_expiry'] = $expiry_date;
        $fields['product_ref'] = $product_ref;
        $fields['subscr_id'] = $subscr_id;

        $id = isset($_POST['edit_record'])?$_POST['edit_record']:'';
        $lk_table = SLM_TBL_LICENSE_KEYS;
        if (empty($id)) {//Insert into database
            $result = $wpdb->insert( $lk_table, $fields);
            $id = $wpdb->insert_id;
            if($result === false){
                $errors .= __('Record could not be inserted into the database!', 'slm');
            }
        } else { //Update record
            $where = array('id'=>$id);
            $updated = $wpdb->update($lk_table, $fields, $where);
            if($updated === false){
                //TODO - log error
                $errors .= __('Update of the license key table failed!', 'slm');
            }
        }

        if(empty($errors)){
            $message = "Record successfully saved!";
            echo '<div id="message" class="alert alert-success"><p>';
            echo $message;
            echo '</p></div>';
        }else{
            echo '<div id="message" class="alert alert-danger">' . $errors . '</div>';            
        }
        
        $data = array('row_id' => $id, 'key' => $license_key);
        do_action('slm_add_edit_interface_save_record_processed',$data);
        
    }

?>    
    <style type="text/css">
        .del{
            cursor: pointer;
            color:red;	
        }
    </style>
    <div class="alert alert-light">You can add a new license or edit an existing one from this interface.
    </div>

    <div class="postbox">
        <h3 class="hndle"><label for="title">License Details </label></h3>
        <div class="inside">

            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                <?php wp_nonce_field('slm_add_edit_nonce_action', 'slm_add_edit_nonce_val' ) ?>
                <table class="form-table table table-bordered">

                    <?php
                    if ($id != '') {
                        echo '<input class="form-control form-control-lg" name="edit_record" type="hidden" value="' . $id . '" />';
                    } else {
                        if(!isset($editing_record)){//Create an empty object
                            $editing_record = new stdClass();
                        }
                        //Auto generate unique key
                        $lic_key_prefix = $slm_options['lic_prefix'];
                        if (!empty($lic_key_prefix)) {
                            $license_key = uniqid($lic_key_prefix);
                        } else {
                            $license_key = uniqid();
                        }
                    }
                    ?>

                    <tr valign="top">
                        <th scope="row">License information </th>
                        <td><label>License Key</label> <a data-tooltip="The unique license key. When adding a new record it automatically generates a unique key in this field for you. You can change this value to customize the key if you like."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="license_key" type="text" id="license_key" value="<?php echo $license_key; ?>" size="30" />
                          

                        </td>
                        <td><label>Max domains allowed </label> <a data-tooltip="Number of domains/installs in which this license can be used."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="max_allowed_domains" type="text" id="max_allowed_domains" value="<?php echo $max_domains; ?>" size="5" />
                        </td>
                        <td><label>Manual reset</label> <a data-tooltip="The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="manual_reset_count" type="text" id="manual_reset_count" value="<?php echo $reset_count; ?>" size="6" />
                        </td>
                        <td> <label>Licence status</label> <a data-tooltip="The license status."><span class="dashicons dashicons-info"></span></a>
                        	<select class="custom-select" name="lic_status" style="color:
								<?php
									if ($license_status == 'pending') echo '#f39c12 !important';
									if ($license_status == 'active') echo '#18bc9c !important';
									if ($license_status == 'blocked') echo '#e74c3c !important';
									if ($license_status == 'expired') echo '#e74c3c !important';
								?>
                                ">    
                                <option value="pending" <?php if ($license_status == 'pending') echo 'selected="selected"'; ?> >Pending</option>
                                <option value="active" <?php if ($license_status == 'active') echo 'selected="selected"'; ?> >Active</option>
                                <option value="blocked" <?php if ($license_status == 'blocked') echo 'selected="selected"'; ?> >Blocked</option>
                                <option value="expired" <?php if ($license_status == 'expired') echo 'selected="selected"'; ?> >Expired</option>
                            </select>
                       </td>
                    </tr>

                    <?php
                    if ($id != '') {
                        global $wpdb;
                        $reg_table = SLM_TBL_LIC_DOMAIN;
                        $sql_prep = $wpdb->prepare("SELECT * FROM $reg_table WHERE lic_key_id = %s", $id);
                        $reg_domains = $wpdb->get_results($sql_prep, OBJECT);
                        ?>
                        <tr valign="top">
                            <th scope="row">Registered Domains</th>
                            <td colspan="4"><?php
                                if (count($reg_domains) > 0) {
                                    ?>
                              <div style="background: red;width: 100px;color:white; font-weight: bold;padding-left: 10px;" id="reg_del_msg"></div>
                              <div style="overflow:auto; height:200px;width:400px;border:1px solid #ccc;">
                                <table cellpadding="0" cellspacing="0">
                                  <?php
                                            $count = 0;
                                            foreach ($reg_domains as $reg_domain) {
                                                ?>
                                  <tr <?php echo ($count % 2) ? 'class="alternate"' : ''; ?>>
                                    <td height="5"><?php echo $reg_domain->registered_domain; ?></td> 
                                    <td height="5"><span class="del" id=<?php echo $reg_domain->id ?>>X</span></td>
                                    </tr>
                                  <?php
                                                $count++;
                                            }
                                            ?>
                                  </table>         
                                </div>
                              <?php
                                } else {
                                    echo "<div class='alert alert-warning'>Not Registered Yet.</div>";
                                }
                                ?>                            </td>
                        </tr>
                    <?php } ?>

                    <tr valign="top">
                        <th scope="row">Company profile</th>
                        <td><label class="">Company name</label> <a data-tooltip="License user's company name"><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="company_name" type="text" id="company_name" value="<?php echo $company_name; ?>" /></td>
                        <td><label class="">First name</label> <a data-tooltip="License user's first name"><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="first_name" type="text" id="first_name" value="<?php echo $first_name; ?>" /></td>
                        <td><label class="">Last name</label> <a data-tooltip="License user's last name"><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="last_name" type="text" id="last_name" value="<?php echo $last_name; ?>" /></td>
                        <td><label class="">Email address</label> <a data-tooltip="License user's email address"><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="email" type="text" id="email" value="<?php echo $email; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Product and Payment</th>
                        <td><label>Product reference</label> <a data-tooltip="The product that this license applies to (if any)."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="product_ref" type="text" id="product_ref" value="<?php echo $product_ref; ?>" /></td>
                        <td><label>Transaction ID</label> <a data-tooltip="The unique transaction ID associated with this license key"><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="txn_id" type="text" id="txn_id" value="<?php echo $txn_id; ?>" /></td>
                        <td colspan="2"><label>Subscriber ID</label> <a data-tooltip="The Subscriber ID (if any). Can be useful if you are using the license key with a recurring payment plan."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="subscr_id" type="text" id="subscr_id" value="<?php echo $subscr_id; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Date License</th>
                        <td><label>Date created</label> <a data-tooltip="Creation date of license."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="date_created" type="date" id="date_created" class="wplm_pick_date" value="<?php echo $created_date; ?>" /></td>
                        <td><label>Date renewed</label> <a data-tooltip="Renewal date of license."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="date_renewed" type="date" id="date_renewed" class="wplm_pick_date" value="<?php echo $renewed_date; ?>" /></td>
                        <td colspan="2"><label>Date expiry</label> <a data-tooltip="Expiry date of license."><span class="dashicons dashicons-info"></span></a><input class="form-control form-control-lg" name="date_expiry" type="date" id="date_expiry" class="wplm_pick_date" value="<?php echo $expiry_date; ?>" /></td>
                    </tr>
                    
                </table>

                <?php
                $data = array('row_id' => $id, 'key' => $license_key);
                $extra_output = apply_filters('slm_add_edit_interface_above_submit','', $data);
                if(!empty($extra_output)){
                    echo $extra_output;
                }
                ?>
                
                <div class="submit">
                    <input type="submit" class="button-primary" name="save_record" value="Save Record" />
                </div>
            </form>
        </div></div>
    <a href="admin.php?page=<?php echo SLM_MAIN_MENU_SLUG; ?>" class="button">Manage Licenses</a><br /><br />
    </div></div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.del').click(function() {
                var $this = this;
                jQuery('#reg_del_msg').html('Loading ...');
                jQuery.get('<?php echo get_bloginfo('wpurl') ?>' + '/wp-admin/admin-ajax.php?action=del_reistered_domain&id=' + jQuery(this).attr('id'), function(data) {
                    if (data == 'success') {
                        jQuery('#reg_del_msg').html('Deleted');
                        jQuery($this).parent().parent().remove();
                    }
                    else {
                        jQuery('#reg_del_msg').html('Failed');
                    }
                });
            });
        });
    </script>
<?php
}
