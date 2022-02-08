<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Application extends Model
{
    use HasFactory;

    public static function check_add_application(
        $name_th,
        $name_en,
        $description_th,
        $description_en,
        $category_id,
        $key_app,
        $type_login,
        $status,
        $status_sso,
        $image,
        $url,
        $user_id
    ) {

        $sql = "
        SELECT * FROM application WHERE
        name_th = '{$name_th}'
        OR name_en = '{$name_en}'";

        $sql_app = DB::select($sql);

        if (count($sql_app) == 0) {
            $app = Application::create_app($name_th, $name_en, $description_th, $description_en, $category_id, $key_app, $type_login, $status, $status_sso, $image, $url, $user_id);
            return response()->json([
                'success' => [
                    'data' => 'Application Created',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Application Duplicate!',
                ]
            ], 400);
        }
    }

    public static function create_app($name_th, $name_en, $description_th, $description_en, $category_id, $key_app, $type_login, $status, $status_sso, $image, $url, $user_id)
    {
        $datetime_now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO application 
        (name_th
        ,name_en
        ,description_th
        ,description_en
        ,category_id
        ,key_app
        ,type_login
        ,status
        ,status_sso
        ,createdate
        ,updatedate
        ,createby
        ,updateby
        ,image
        ,url
        ,active)
        VALUES 
        ('{$name_th}'
        ,'{$name_en}'
        ,'{$description_th}'
        ,'{$description_en}'
        , {$category_id}
        ,'{$key_app}'
        , {$type_login}
        , {$status}
        , {$status_sso}
        ,'{$datetime_now}'
        ,'{$datetime_now}'
        ,'{$user_id}'
        ,'{$user_id}'
        ,'{$image}'
        ,'{$url}'
        , 1)";

        $sql_app = DB::insert($sql);

        $datas = array();
        if (!empty($sql_app)) {
            $datas = $sql_app;
        }
        return $datas;
    }


    public static function update_application(
        $app_id,
        $name_th,
        $name_en,
        $description_th,
        $description_en,
        $category_id,
        $key_app,
        $type_login,
        $status,
        $status_sso,
        $image,
        $url,
        $user_id
    ) {

        $sql = "
        SELECT * FROM application WHERE
        app_id = '{$app_id}'";

        $sql_app = DB::select($sql);

        if (count($sql_app) == 1) {
            $sql_name = "
            SELECT * FROM application WHERE
            name_th = '{$name_th}'
            OR name_en = '{$name_en}'";

            $sql_check_name = DB::select($sql_name);

            if (count($sql_check_name) == 0) {
                $app = Application::update_app($app_id, $name_th, $name_en, $description_th, $description_en, $category_id, $key_app, $type_login, $status, $status_sso, $image, $url, $user_id);
                return response()->json([
                    'success' => [
                        'data' => 'Application Updated',
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'data' => 'Validate Name Application!',
                    ]
                ], 400);
            }
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Validate Application!',
                ]
            ], 400);
        }
    }

    public static function update_app($app_id, $name_th, $name_en, $description_th, $description_en, $category_id, $key_app, $type_login, $status, $status_sso, $image, $url, $user_id)
    {
        $datetime_now = date('Y-m-d H:i:s');
        $sql = "UPDATE application SET
         name_th = '{$name_th}'
        ,name_en = '{$name_en}'
        ,description_th = '{$description_th}'
        ,description_en = '{$description_en}'
        ,category_id = {$category_id}
        ,key_app = '{$key_app}'
        ,type_login = {$type_login}
        ,status = {$status}
        ,status_sso = {$status_sso}
        ,updatedate = '{$datetime_now}'
        ,updateby = '{$user_id}'
        ,image = '{$image}'
        ,url = '{$url}'
        ,active = 1
        WHERE app_id = {$app_id}";

        $sql_app = DB::insert($sql);

        $datas = array();
        if (!empty($sql_app)) {
            $datas = $sql_app;
        }
        return $datas;
    }

    public static function delete_application($app_id, $name_th, $name_en, $user_id)
    // , $description_th, $description_en, $category_id, $type_login, $status, $status_sso, $image, $url, $emp_code)
    {
        $sql_check = "SELECT * FROM application
        WHERE app_id = {$app_id}";
        $sql_check_id = DB::select($sql_check);

        if (count($sql_check_id) == 1) {
            $sql = "SELECT group_id FROM application_group
            WHERE app_id like '%{$app_id}%'";

            $sql_app = DB::select($sql);

            if (!empty($sql_app)) {
                foreach ($sql_app as $item) {
                    $id[] = array(
                        'group_id' => $item->group_id,
                    );
                }
                $group = implode(', ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id));

                $sql = "SELECT * FROM user_profile
            WHERE active = 1 AND group_id in ($group)";

                $sql_group = DB::select($sql);
                if (count($sql_group) > 0) {
                    return response()->json([
                        'error' => [
                            'data' => 'Application active!',
                        ]
                    ], 400);
                }
            } else {
                $datetime_now = date('Y-m-d H:i:s');
                $sql = "UPDATE application SET
            active = 0,
            updateby = '{$user_id}',
            updatedate = '{$datetime_now}'
            WHERE app_id = {$app_id}";

                $sql_app = DB::select($sql);
                return response()->json([
                    'success' => [
                        'data' => 'Application Deleted',
                    ]
                ], 200);
            }
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Validate App ID!',
                ]
            ], 400);
        }
    }

    public static function get_application($keyword, $field, $sort)
    {
        $search = '';
        $order_by = '';
        if ($keyword != '') {
            $search = "AND ((app.name_th like '%{$keyword}%') OR (app.name_en like '%{$keyword}%'))";
        }
        if ($field != '') {
            $order_by = "ORDER BY {$field} {$sort}";
        } else {
            $order_by = "ORDER BY app.name_en,app.name_th";
        }
        $sql = "
        SELECT app.app_id
        ,app.name_th
        ,app.name_en
        ,app.description_th
        ,app.description_en
        ,cat.name_th as category_name_th
        ,cat.name_en as category_name_en
        ,app.category_id
        ,app.key_app
        ,app.type_login
        ,app.status_sso
        ,app.status
        ,app.image
        ,app.url
        FROM application app
        JOIN category cat 
        ON app.category_id=cat.category_id
        WHERE app.active = 1
        {$search}
        {$order_by}
        ";

        $sql_app = DB::select($sql);

        $datas = array();
        if (!empty($sql_app)) {
            $i = 0;
            foreach ($sql_app as $item) {
                $datas[] = array(
                    'index'  => $i,
                    'app_id' => $item->app_id,
                    'name_th' => $item->name_th,
                    'name_en' => $item->name_en,
                    'description_th' => $item->description_th,
                    'description_en' => $item->description_en,
                    'category_name_th' => $item->category_name_th,
                    'category_name_en' => $item->category_name_en,
                    'category_id' => $item->category_id,
                    'key_app' => $item->key_app,
                    'type_login' => $item->type_login,
                    'status_sso' => $item->status_sso,
                    'status' => $item->status,
                    'image' => $item->image,
                    'url' => $item->url,
                );
                $i++;
            }
        }
        return $datas;
    }

    public static function get_category($keyword, $field, $sort)
    {
        $search = '';
        $order_by = '';
        if ($keyword != '') {
            $search = "AND ((name_th like '%{$keyword}%') OR (name_en like '%{$keyword}%'))";
        }
        if ($field != '') {
            $order_by = "ORDER BY {$field} {$sort}";
        } else {
            $order_by = "ORDER BY name_en,name_th";
        }
        $sql = "
        SELECT * FROM category WHERE active = 1
        {$search}
        {$order_by}";

        $sql_cat = DB::select($sql);

        $datas = array();
        if (!empty($sql_cat)) {
            $i = 0;
            foreach ($sql_cat as $item) {
                $datas[] = array(
                    'index' => $i,
                    'category_id' => $item->category_id,
                    'name_th' => $item->name_th,
                    'name_en' => $item->name_en,
                );
                $i++;
            }
        }
        return $datas;
    }

    public static function add_category($name_th, $name_en, $user_id)
    {
        $sql = "
        SELECT * FROM category WHERE
        name_th = '{$name_th}'
        OR name_en = '{$name_en}'";

        $sql_cat = DB::select($sql);

        if (count($sql_cat) == 0) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "INSERT INTO category 
            (name_th
            ,name_en
            ,createdate
            ,updatedate
            ,createby
            ,updateby
            ,active)
            VALUES
            ('{$name_th}'
            ,'{$name_en}'
            ,'{$datetime_now}'
            ,'{$datetime_now}'
            ,'{$user_id}'
            ,'{$user_id}'
            ,1)";

            $sql_cat = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Catagory Created',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Catagory Duplicate!',
                ]
            ], 400);
        }
    }

    public static function update_category($category_id, $name_th, $name_en, $user_id)
    {

        $sql = "
        SELECT * FROM category WHERE
        category_id = '{$category_id}'";

        $sql_cat = DB::select($sql);

        if (count($sql_cat) == 1) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "UPDATE category SET 
             name_th = '{$name_th}'
            ,name_en = '{$name_en}'
            ,updatedate = '{$datetime_now}'
            ,updateby = '{$user_id}'
            WHERE category_id = $category_id";

            $sql_cat = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Catagory Updated',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Validate Catagory!',
                ]
            ], 400);
        }
    }

    public static function delete_category($category_id, $name_th, $name_en, $user_id)
    {

        $sql = "
        SELECT * FROM category WHERE
        category_id = '{$category_id}'";

        $sql_cat = DB::select($sql);

        if (count($sql_cat) == 1) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "UPDATE category SET 
             active = 0
            ,updatedate = '{$datetime_now}'
            ,updateby = '{$user_id}'
            WHERE category_id = $category_id";

            $sql_cat = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Catagory Deleted',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Validate Catagory!',
                ]
            ], 400);
        }
    }

    public static function get_group_app($keyword, $field, $sort)
    {
        $search = '';
        $where_sort = '';
        if ($keyword != '') {
            $search = "AND ((app.name_th like '%{$keyword}%') OR (app.name_en like '%{$keyword}%'))";
        }
        if ($field != '') {
            $where_sort = "ORDER BY {$field} {$sort}";
        }else{
            return response()->json([
                'error' => [
                    'data' => 'ไม่มีการส่งตัวแปร field sort จากหน้าบ้าน',
                ]
            ], 400);
            die;
        }
        $sql_gp = "
        SELECT app.group_id, 
        app.name_th as group_name_th,  
        app.name_en as group_name_en,
        app.app_id
        FROM application_group app
        WHERE  app.active = 1
        {$search}
        {$where_sort}
        ";

        $sql_group = DB::select($sql_gp);


        if (!empty($sql_group)) {
            $i = 0;
            foreach ($sql_group as $item) {

                $sql_total_app = "
                SELECT count(app_id) as total_app FROM application 
                WHERE app_id in ($item->app_id)
                AND active = 1
                ";

                $total_app = DB::select($sql_total_app);

                foreach ($total_app as $item_a) {

                    $sql_total_user = "
                      SELECT count(user_id) as total_user FROM user_profile 
                      WHERE group_id = $item->group_id
                      AND active = 1
                      ";

                    $total_user = DB::select($sql_total_user);
                    foreach ($total_user as $item_u) {
                        $datas[] = array(
                            'index'       => $i,
                            'group_id'    => $item->group_id,
                            'name_th'     => $item->group_name_th,
                            'name_en'     => $item->group_name_en,
                            'total_user'  => $item_u->total_user,
                            'total_app'   => $item_a->total_app,
                        );
                    }
                }
                $i++;
            }
        } else {
            $datas[] = array();
        }

        return response()->json([
            'success' => [
                'data' => $datas,
            ]
        ], 200);
    }


    public static function groupdetail($group_id)
    {
        $sql_gp = "
        SELECT app.group_id, 
        app.name_th as group_name_th,  
        app.name_en as group_name_en,
        app.app_id
        FROM application_group app
        WHERE  app.group_id = {$group_id}
        ";

        $sql_group = DB::select($sql_gp);


        if (!empty($sql_group)) {
            foreach ($sql_group as $item) {

                $sql_app = "
                SELECT a.*,
                c.name_th category_name_th, 
                c.name_en category_name_en 
                FROM application a 
                INNER JOIN category c 
                ON 
                a.category_id = c.category_id
                WHERE a.app_id in ($item->app_id)
                AND a.active = 1
                ";

                $app = DB::select($sql_app);
                $app_a = array();
                foreach ($app as $item_a) {
                    array_push($app_a, $item_a);
                }
                $datas = array(
                    'group_id'    => $item->group_id,
                    'name_th'     => $item->group_name_th,
                    'name_en'     => $item->group_name_en,
                    'app'         => $app_a,
                );
            }
        } else {
            $datas[] = array();
        }

        return $datas;
    }

    public static function dropdown_group($group_id, $user_id)
    {
        if ($user_id == '') {
            return response()->json([
                'error' => [
                    'data' => 'ไม่ได้ส่ง user_id',
                ]
            ], 400);
            die;
        }
        $sql_gp = "
        SELECT app.group_id, 
        app.name_th as group_name_th,  
        app.name_en as group_name_en,
        app.app_id
        FROM application_group app
        WHERE  app.group_id = {$group_id}
        ";

        $sql_group = DB::select($sql_gp);


        if (!empty($sql_group)) {
            foreach ($sql_group as $item) {

                $sql_app = "
                SELECT a.*,
                c.name_th category_name_th, 
                c.name_en category_name_en,
                IFNULL(ss.username, '') as username
                FROM application a 
                INNER JOIN category c 
                ON 
                a.category_id = c.category_id
                LEFT JOIN sso ss
                ON 
                a.app_id = ss.app_id AND ss.user_id = {$user_id}
                WHERE a.app_id in ($item->app_id)
                AND a.active = 1
                AND a.status_sso = 1
                ";

                $app = DB::select($sql_app);
                $app_a = array();
                foreach ($app as $item_a) {
                    array_push($app_a, $item_a);
                }
                $datas = array(
                    'group_id'    => $item->group_id,
                    'name_th'     => $item->group_name_th,
                    'name_en'     => $item->group_name_en,
                    'app'         => $app_a,
                );
            }
        } else {
            $datas[] = array();
        }

        return response()->json([
            'success' => [
                'data' => $datas,
            ]
        ], 200);
    }

    public static function add_group_app($name_th, $name_en, $app_id, $user_id)
    {

        $sql = "
        SELECT * FROM application_group WHERE
        name_th = '{$name_th}' OR name_en = '{$name_en}'";

        $sql_group = DB::select($sql);

        if (count($sql_group) == 0) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "INSERT INTO application_group
            (name_th,
            name_en,
            app_id,
            createdate,
            updatedate,
            createby,
            updateby,
            active)
            VALUES
            ('{$name_th}',
            '{$name_en}',
            '{$app_id}',
            '{$datetime_now}',
            '{$datetime_now}',
            '{$user_id}',
            '{$user_id}',
            1)";

            $sql_group = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Group Created',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => 'Duplicate group!',
                ]
            ], 400);
        }
    }

    public static function update_group_app($group_id, $name_th, $name_en, $app_id, $user_id)
    {

        $sql = "
        SELECT * FROM application_group WHERE
        group_id = {$group_id}
        AND active = 1";

        $sql_group = DB::select($sql);

        if (count($sql_group) == 1) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "UPDATE application_group SET
            name_th = '{$name_th}',
            name_en = '{$name_en}',
            app_id  = '{$app_id}',
            updatedate = '{$datetime_now}',
            updateby   = '{$user_id}'
            WHERE group_id = {$group_id}
            AND active = 1
            ";

            $sql_group = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Group Updated',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => ' Validate group!',
                ]
            ], 400);
        }
    }


    public static function delete_group_app($group_id, $name_th, $name_en, $user_id)
    {

        $sql_g = "
        SELECT * FROM application_group WHERE
        group_id = '{$group_id}'
        AND active = 1";

        $sql_group = DB::select($sql_g);

        $sql_u = "
        SELECT * FROM user_profile WHERE
        group_id = '{$group_id}'
        AND active = 1";

        $sql_user = DB::select($sql_u);

        if (count($sql_group) == 1 && count($sql_user) == 0) {
            $datetime_now = date('Y-m-d H:i:s');
            $sql = "UPDATE application_group SET
            active  = 0,
            updatedate = '{$datetime_now}',
            updateby   = '{$user_id}'
            WHERE group_id = {$group_id}
            ";

            $sql_group = DB::select($sql);
            return response()->json([
                'success' => [
                    'data' => 'Group Deleted',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'data' => ' User using group!',
                ]
            ], 400);
        }
    }
}
