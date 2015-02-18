<?
namespace oxygen\common\auth\entity\user_role;
    use oxygen\entity\Entity;

    class UserRole extends Entity {
        static public function __class_construct() {
            self::prefix('oxygen\\common\\auth\\entity\\Entity');
            self::source('auth_user_roles','ur');
            self::prefix('oxygen\\field\\Field');
            self::field('Object','user');
            self::field('Object','role');
            self::field('JSON','role_args');
        }
    }

?>