<?php
class Forgot_password extends Trongate {

    function index() {
        //change the settings below to suit your needs
        ///////////////////////////////////////////////
        $settings['table'] = 'members';
        $settings['template'] = 'public';
        $settings['fields'] = array('username', 'email_address');
        $settings['update_password_url'] = 'members/update_password';
        ///////////////////////////////////////////////

        if ((segment(2) == 'invalid_url') || (segment(2) == 'reset_password')) {
            return $settings;
        } else {
            if (ENV == 'dev') {
                extract($settings);
                $link = BASE_URL.'forgot_password/help/'.$table.'/'.$template.'/';
                foreach($fields as $field) {
                    $link.= $field.'-';
                }
                $link.= '~';
                $data['link'] = str_replace('-~', '', $link);
                $data['link'] = $this->_encrypt_link($data['link']);
                $this->view('show_link', $data);
            } else {
                redirect(BASE_URL);
            }
        }

    }

    function _send_email($account_obj, $reset_url) {
        //send an email inviting the user to goto the $reset url

        //***************************************** 
        // *** ADD YOUR EMAIL SENDING CODE HERE ***
        //*****************************************
    }

    function _encrypt_link($link) {
        $ditch = BASE_URL.'forgot_password/help/';
        $str = str_replace($ditch, '', $link);
        $this->module('forgot_password-encryption');
        $enc = $this->encryption->_encrypt($str);
        $enc_link = $ditch.$enc;
        return $enc_link;
    }

    function _decrypt_link($this_url) {
        $ditch = BASE_URL.segment(1).'/'.segment(2).'/';
        $str = str_replace($ditch, '', $this_url);
        $this->module('forgot_password-encryption');
        $decrypted = $this->encryption->_decrypt($str);
        $decrypted_link = $ditch.$decrypted;
        return $decrypted_link;
    }

    function _in_you_go($trongate_user_id) {
        //sets a cookie for 60 days, logs user in then sends user to 'update password' URL
        $this->module('trongate_tokens');
        $token_data['user_id'] = $trongate_user_id;
        $thirty_days = 86400*60; //number of seconds in 60 days
        $nowtime = time(); // unix timestamp
        $token_data['expiry_date'] = $nowtime+$thirty_days; //60 days ahead as a timestamp
        $token_data['set_cookie'] = true;
        $this->trongate_tokens->_generate_token($token_data); //generate toke & set cookie

        $settings = $this->index();
        $update_password_url = $settings['update_password_url'];
        $update_password_url = str_replace(BASE_URL, '', $update_password_url);
        redirect(BASE_URL.$update_password_url);
    }

    function reset_password() {
        //make sure the code is valid then...
        //log the user in and redirect to the password reset page...
        $token = segment(3);
        $this->_clear_table();

        //is the token valid?
        $result = $this->model->get_one_where('code',  $token, 'forgot_password');    
        
        if ($result == false) {
            redirect('forgot_password/invalid_url');
        } else {
            $target_trongate_user_id = $result->trongate_user_id;
            //log this user in (long term)
            $this->_in_you_go($target_trongate_user_id);
        }

    }

    function help() {
        $assumed_url = $this->_decrypt_link(current_url());
        $template = $this->_get_template($assumed_url);
        $fields = $this->_get_fields($assumed_url);
        $table = $this->_get_table($assumed_url);
        $this->_make_sure_valid_url($table, $fields);
        $data['fields'] = $fields;
        $data['form_intro'] = $this->_build_form_intro($fields);
        $data['form_location'] = str_replace('/help', '/submit_details', current_url());
        $data['view_file'] = 'first_form';
        $this->template($template, $data);
    }

    function check_your_email() {
        $data['view_file'] = 'check_your_email';
        $assumed_url = $this->_decrypt_link(current_url());
        $template = $this->_get_template($assumed_url);
        $this->template($template, $data);
    }

    function invalid_url() {
        $data['view_file'] = 'invalid_url';
        $settings = $this->index();
        $template = $settings['template'];
        $this->template($template, $data);
    }

    function submit_details() {
        $submit = post('submit');
        $assumed_url = $this->_decrypt_link(current_url());
        $fields = $this->_get_fields($assumed_url);
        $table = $this->_get_table($assumed_url);
        $this->_make_sure_valid_url($table, $fields);

        if ($submit == 'Submit') {

            //make sure each field is trimmed and safe
            foreach($fields as $field) {
                $params[$field] = post($field, true);
                $field_label = str_replace('_', ' ', $field);

                if (strlen($params[$field]) == 0) {
                    $validation_errors[] = 'The '.$field_label.' cannot be empty';
                }
            }

            if (!isset($validation_errors)) {
                //make sure that this is a valid table record 
                $sql = 'SELECT * from '.$table.' WHERE ';
                foreach($params as $param_key => $param_value) {
                    $sql.= $param_key.'=:'.$param_key.' AND ';
                }
                $sql.= '~';
                $sql = str_replace(' AND ~', '', $sql);
                $rows =  $this->model->query_bind($sql, $params, 'object');
                $num_rows = count($rows);

                if ($num_rows == 0) {
                    if (count($fields) == 1) {
                        $field_label = str_replace('_', ' ', $fields[0]);
                        $error_msg = 'The '.$field_label.' that you submitted was not valid.';
                    } else {
                        $error_msg = 'The details that you submitted were not valid';
                    }

                    $validation_errors[] = $error_msg;
                }
            }

            if (isset($validation_errors)) {
                $_SESSION['form_submission_errors'] = $validation_errors;
                $this->help();
            } else {
                $this->_init_password_reset($rows[0]);
            }

        }
    }

    function _check_email_dev_shortcut() {
        $sql = 'SELECT * from forgot_password order by date_created desc limit 0,1';
        $rows =  $this->model->query($sql, 'object');
        $num_rows = count($rows);

        if (($num_rows == 1) && (ENV == 'dev')) {
            $token = $rows[0]->code;
            $data['reset_url'] = BASE_URL.'forgot_password/reset_password/'.$token;
            $this->view('email_email_dev_shortcut', $data);
        }
    }

    function _init_password_reset($account_obj) {
        //get the trongate_user_id of the user the create a password reset token
        $trongate_user_id = $account_obj->trongate_user_id;
        $reset_url = $this->_create_reset_url($trongate_user_id);
        $this->_send_email($account_obj, $reset_url);
        $target_url = str_replace('/submit_details/', '/check_your_email/', current_url());
        redirect($target_url);
    }

    function _create_reset_url($trongate_user_id) {
        $this->_clear_table($trongate_user_id);
        $data['date_created'] = time();
        $data['trongate_user_id'] = $trongate_user_id;
        $data['code'] = make_rand_str(32);
        $this->model->insert($data, 'forgot_password');
        $reset_url = BASE_URL.'forgot_password/reset_password/'.$data['code'];
        return $reset_url;
    }

    function _clear_table($trongate_user_id=null) {
        if (isset($trongate_user_id)) {
            $params['trongate_user_id'] = $trongate_user_id;
            $sql = 'delete from forgot_password where trongate_user_id=:trongate_user_id';
        } else {
            //delete old reset requests
            $six_hours = 21600;
            $nowtime = time();
            $params['ancient_history'] = $nowtime-$six_hours;
            $sql = 'delete from forgot_password where date_created<:ancient_history';
        }

        $this->model->query_bind($sql, $params);

        //attempt ID reset if no rows on table
        $count = $this->model->count('forgot_password');

        if ($count == 0) {
            $sql2 = 'truncate forgot_password';
            $this->model->query($sql2);
        }

    }

    function _build_form_intro($fields) {
        $info = 'Enter your [fields] below, then hit \'Submit\'.';
        if (count($fields) == 1) {
            $info = str_replace('[fields]', $fields[0], $info);
        } else {
            $str = '';
            foreach($fields as $field) {
                $str.= $field.' and ';
            }

            $str.= '~';
            $str = str_replace(' and ~', '', $str);
            $info = str_replace('[fields]', $str, $info);
        }

        $info = str_replace('_', ' ', $info);
        return $info;
    }

    function _get_table($assumed_url) {
        $str = str_replace(BASE_URL, '', $assumed_url);
        $bits =  explode('/', $str);
        $table = $bits[2];
        return $table;
    }

    function _get_template($assumed_url) {
        $str = str_replace(BASE_URL, '', $assumed_url);
        $bits =  explode('/', $str);
        $template = $bits[3];
        return $template;
    }

    function _get_fields($assumed_url) {
        $str = str_replace(BASE_URL, '', $assumed_url);
        $bits =  explode('/', $str);
        $fields_str = $bits[4];
        $fields = explode('-', $fields_str);
        return $fields;
    }

    function _make_sure_valid_url($table, $fields) {
        $table_exists = $this->_make_sure_table_exists($table);
        $valid_columns = $this->_make_sure_columns_valid($table, $fields);

        if (($table_exists == true) && ($valid_columns == true)) {
            return true;
        } else {
            http_response_code(400);
            echo 'Invalid URL'; die();
        }

    }

    function _make_sure_table_exists($table) {
        $all_tables = $this->_get_all_tables();
        if(!in_array($table, $all_tables)) {
            return false;
        } else {
            return true;
        }
    }

    function _make_sure_columns_valid($table, $fields) {
        $valid = true;
        $all_table_columns = $this->_get_all_columns($table);

        foreach($fields as $field) {
            if(!in_array($field, $all_table_columns)) {
                $valid = false;
            }
        }

        return $valid;
    }

    function _get_all_tables() {
        $tables = [];
        $sql = 'show tables';
        $column_name = 'Tables_in_'.DATABASE;
        $rows = $this->model->query($sql, 'array');
        foreach ($rows as $row) {
            $tables[] = $row[$column_name];
        }

        return $tables;
    }

    function _get_all_columns($table) {

        $columns = [];
        $sql = 'describe '.$table;
        $rows = $this->model->query($sql, 'array');
        foreach ($rows as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;   
    }

}