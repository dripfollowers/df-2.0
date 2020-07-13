<?php

namespace DripFollowers\Common;

abstract class PacksTypes {
    const Automatic_Followers = 'automatic-followers';
    const Instant_Followers = 'instant-followers';
    const Instant_Views = 'instant-views';
    const Instant_Likes = 'instant-likes';
    const Automatic_Likes = 'automatic-likes';
    const Split_Likes = 'Split-likes';
   
    static $types = array (self::Automatic_Followers,self::Instant_Followers,self::Instant_Likes,self::Instant_Views,self::Automatic_Likes,self::Split_Likes);

    static function is_valide_type($type) {
        return in_array ( $type, self::$types );
    }
}
