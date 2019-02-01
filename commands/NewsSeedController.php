<?php
namespace app\commands;

use app\models\News;
use Da\User\Model\User;
use Yii;
use yii\console\Controller;

class NewsSeedController extends Controller
{
    public function actionIndex()
    {
        self::seedUser();

        $faker = \Faker\Factory::create('ru_RU');

        for ( $i = 1; $i <= 25; $i++ ) {
            $news = new News();
            $news->title = $faker->text(20).' '.$i.PHP_EOL;
//            $news->img = $faker->text(10).' '.$i;
            $news->short_text = $faker->text(150);
            $news->news_text = $faker->text();
            $news->created_at = date('Y-m-d H:i:s');
            $news->created_by = $i % 2 ? 1 : 3;
            $news->is_active = $i % 3 ? 0 : 1;
            $news->save();
        }

        self::seedAuthAndRoleTables();
    }

    private static function seedUser() {
        $fields = ['username', 'email', 'password_hash', 'auth_key', 'unconfirmed_email', 'registration_ip', 'flags',
            'confirmed_at', 'blocked_at', 'updated_at', 'created_at', 'last_login_at', 'auth_tf_key', 'auth_tf_enabled',
            'password_changed_at'];
        $time = time();
        $values = [
            ['administrator', 'administrator@yii2.ru', '$2y$10$9AB9xjCLzLMeS1g3PA7SbuOlRgGDENHxFveY1OubCdT3eEECCgxeO',
                'S2JFMs5ftHy6ivTW9PkI2U7IpQKP3Xq-', null, '127.0.0.1', 0, $time + 1, null, $time, $time, $time + 2,
                '', 0, $time],
            ['registered', 'registered@yii2.ru', '$2y$10$LObBfQXfdCAo0oxclRIrUeGmjjjbmNYdDl7415DDrTD2Snam31sT2',
                '-FNeba0bYU_F2awZtQFAeD_2Rwzu4wZA', null, '127.0.0.1', 0, $time + 2, null, $time, $time, $time + 3,
                '', 0, $time],
            ['manager', 'manager@yii2.ru', '$2y$10$TrNwyjS0CDvfD3n7du4k2OGNHMGWzN5StbeiQLApSgeXXnF60h7GW',
                'AuLF_s5g2Pz7mlHe5AvQQ4XAn3y0ycvO', null, '127.0.0.1', 0, $time + 3, null, $time, $time, $time + 4,
                '', 0, $time],
        ];
        $modelsAmount = count($values);
        for ( $i = 0; $i < $modelsAmount; $i++ ) {
            $user = new User();
            foreach ($fields as $key => $field) {
                $user->{$field} = $values[$i][$key];
            }
            $user->save();
        }

    }

    private static function seedAuthAndRoleTables()
    {
        $time = time();
        Yii::$app->db->createCommand("
                INSERT INTO `auth_rule` (`name`, `data`, `created_at`, `updated_at`) VALUES
                ('isAuthor', 0x4f3a33313a226170705c636f6e74726f6c6c6572735c726261635c417574686f7252756c65223a333a7b733a343a226e616d65223b733a383a226973417574686f72223b733a393a22637265617465644174223b693a313534383932383239313b733a393a22757064617465644174223b693a313534383932383239313b7d, {$time}, {$time})
            ")->execute();
        $time += 5;
        Yii::$app->db->createCommand("
                INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
                ('admin', 1, 'can everything', NULL, NULL, {$time}, {$time}),
                ('manager', 1, 'can add/edit/delete news but own only', NULL, NULL, {$time}, {$time}),
                ('updateNews', 2, 'can update any news', NULL, NULL, {$time}, {$time}),
                ('updateOwnNews', 2, 'can update own news', 'isAuthor', NULL, {$time}, {$time})            
            ")->execute();
        $time += 1;
        Yii::$app->db->createCommand("
                INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
                ('admin', '1', {$time}),
                ('manager', '3', {$time})
            ")->execute();
        $time += 2;
        Yii::$app->db->createCommand("
                INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
                ('admin', 'updateNews'),
                ('manager', 'updateOwnNews'),
                ('updateOwnNews', 'updateNews')
            ")->execute();
    }
}