<?
namespace oxygen\common\auth\entity\user_role;
    use oxygen\entity\Oxygen_Entity;

    class Oxygen_Common_Auth_Entity_UserRole extends Oxygen_Entity {
        public function __class_construct() {
            self::prefix('Oxygen_Common_Auth_Entity');
            self::source('auth_user_roles','ur');
            self::prefix('Oxygen_Field');
            self::field('Object','user');
            self::field('Object','role');
            self::field('JSON','role_args');
        }
    }

?>