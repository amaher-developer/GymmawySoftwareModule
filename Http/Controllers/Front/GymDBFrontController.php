<?php
namespace Modules\Software\Http\Controllers\Front;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\File;
use mysql_xdevapi\Exception;

class GymDBFrontController  extends GymGenericFrontController {

    public function backupDB()
    {
        /* backup the db OR just a table */
        $host = env('DB_HOST');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $DbName = env('DB_DATABASE');
        $port = env('DB_PORT');
        $tables = 'sw_gym_activities,sw_gym_block_members,sw_gym_members,sw_gym_member_subscription,sw_gym_money_boxes,sw_gym_non_members,sw_gym_orders,sw_gym_subscriptions,sw_gym_users';

        $link = mysqli_connect($host, $user, $pass, $DbName, $port);
        $link->set_charset("utf8");
        //get all of the tables
        if ($tables == '*') {
            $tables = array();
            $result = mysqli_query($link, 'SHOW TABLES');
            while ($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }
        //cycle through
        $return = '';
        foreach ($tables as $table) {
            $result = mysqli_query($link, 'SELECT * FROM ' . $table);
            $num_fields = mysqli_num_fields($result);

//            $return .= 'DROP TABLE ' . $table . ';';
//            $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
//            $return .= "\n\n" . $row2[1] . ";\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    if($row) {
                        $return .= 'INSERT INTO ' . $table . ' VALUES(';
                        for ($j = 0; $j < $num_fields; $j++) {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = str_replace("\n", "\\n", $row[$j]);
                            if (isset($row[$j])) {
                                $return .= '"' . $row[$j] . '"';
                            } else {
                                $return .= '""';
                            }
                            if ($j < ($num_fields - 1)) {
                                $return .= ',';
                            }
                        }
                        $return .= ");\n";
                    }
                }
            }
            $return .= "\n\n\n";
        }

        $this->curlBackup(request('email'), request('password'), $return);

    }

    public function curlBackup($email, $password, $db){

        $post = array('backup' => $db, 'email' => $email, 'password' => $password);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('BACKUP_DATABSE_URL'));
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response =curl_exec($ch);
        curl_close ($ch);
        return (int)$response;
    }
}

?>
