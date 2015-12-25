<?php defined('SYSPATH') OR die('No Direct Script Access');

Class Model_User extends Model
{
    public $id;
    public $name        = '';
    public $photo       = '';
    public $photo_big   = '';
    public $photo_small = '';
    public $dt_create;
    public $dt_update;
    public $vk_id       = 0;
    public $fb_id       = 0;
    public $vk_uri      = '';
    public $fb_uri      = '';
    public $github_id   = 0;
    public $github_uri  = '';
    public $bio         = '';
    public $role        = 1;
    public $is_removed  = 0;



    const ROLE_ANY = 0;
    const ROLE_USER = 1;
    const ROLE_ADMIN = 3;


    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Возвращает модель пользователя по его уникальному атрибуту
     * @param string $attr
     * @param int $value
     * @return Model_User
     */
    public static function findByAttribute($attr = 'id', $value = 0)
    {
        $user = DB::select()->from('Users')->where($attr, '=', $value)->execute()->current();

        return self::rowToModel($user);
    }

    /**
     * Возвращает модель пользователя по его id
     * @param int $id
     * @return Model_User
     */
    public static function get($id = 0)
    {
        return self::findByAttribute('id', $id);
    }

    /**
     * Получает из хранилища данных информацию о всех пользователях и превращает её в экземпляры модели
     */
    public static function getAll()
    {
        $users_rows = DB::select()->from('Users')->limit(200)->execute()->as_array();    // TODO(#40) add pagination

        $users = array();

        if (!empty($users_rows)) {
            foreach ($users_rows as $user_row) {
                $user = self::rowToModel($user_row);
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @param $user
     * @return Model_User
     */
    private static function rowToModel($user)
    {
        $model = new Model_User();
        if (!empty($user['id'])) {
            $model->id            = $user['id'];
            $model->name          = $user['name'];
            $model->photo         = $user['photo'];
            $model->photo_small   = $user['photo_small'];
            $model->photo_big     = $user['photo_big'];
            $model->dt_create     = $user['dt_create'];
            $model->dt_update     = $user['dt_update'];
            $model->github_id     = $user['github_id'];
            $model->github_uri    = $user['github_uri'];
            $model->vk_uri        = $user['vk_uri'];
            $model->fb_uri        = $user['fb_uri'];
            $model->instagram_uri = $user['instagram_uri'];
            $model->bio           = $user['bio'];
            $model->role          = $user['role'];
            $model->is_removed    = $user['is_removed'];
        }

        return $model;
    }

    /**
     * Удаляет из базы данного пользователя.
     */
    public function remove()
    {
        if ($this->id != 0) {

            DB::update('Users')->where('id', '=', $this->id)
                ->set(array('is_removed' => 1))
                ->execute();

            // Пользователь удалена
            $this->id = 0;
        }
    }

    /**
     * Возвращает статус заполненности модели
     * @return bool
     */
    public function is_empty()
    {
        return empty($this->id);
    }

    /**
     * Создает новую запись в БД
     * @return true, если данные успешно записаны в БД
     */
    public function save($social=null)
    {
        $result = false;

        if ($social == "vk")
        {
            $result = DB::insert('Users', array('name', 'vk_id',
                'photo', 'photo_small', 'photo_big', 'role', 'is_removed'))->
            values(array($this->name, $this->vk_id,
                $this->photo, $this->photo_small, $this->photo_big, $this->role, $this->is_removed))
                ->execute();
        }
        else
        {
            $result = DB::insert('Users', array('name', 'github_id', 'github_uri',
                'photo', 'photo_small', 'photo_big', 'role', 'is_removed'))->
            values(array($this->name, $this->github_id, $this->github_uri,
                $this->photo, $this->photo_small, $this->photo_big, $this->role, $this->is_removed))
                ->execute();
        }

        if ($result)
            return $result;
        else
            return false;
    }


    /**
     * Обновляет запись в БД
     * @return true, если данные успешно записаны в БД
     */
    public function update()
    {
        if (DB::update('Users')->set(array(
            'name'          => $this->name,
            'github_id'     => $this->github_id,
            'github_uri'    => $this->github_uri,
            'photo'         => $this->photo,
            'dt_update'     => $this->dt_update,        // TODO(#38) trigger
            'photo_small'   => $this->photo_small,
            'photo_big'     => $this->photo_big,
            'role'          => $this->role,
            'is_removed'    => $this->is_removed,
            'vk_uri'        => $this->vk_uri,
            'fb_uri'        => $this->fb_uri,
            'instagram_uri' => $this->instagram_uri,
            'bio'           => $this->bio
        ))->where('id', '=', $this->id)->execute()
        )
            return true;
        else
            return false;
    }

    public function checkAccess($roles)
    {
        foreach ($roles as $role)
        {
            if ($role == Model_User::ROLE_ANY || $role == $this->role)
                return true;
        }
        return false;
    }


    /**
     * Возвращает массив опубликованных статей пользователя
     * @return true, если данные успешно записаны в БД
     */
    public function get_articles_list()
    {
        return Model_Article::getByUserId($this->id);
    }


    /**
    * Метод заносит переданные данные о юзере в модель и базу
    * @param $fields - ассоциативный массив "название поля" - "значение"
    */
    public function edit($fields = array())
    {
        // занесение данных в модель
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }

        // занесения данных в бд
        $this->update();
    }
}
