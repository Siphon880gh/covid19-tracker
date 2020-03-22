<?php  function lcr5e74eae2b6133ifvar($cx, $v, $zero) {
  return ($v !== null) && ($v !== false) && ($zero || ($v !== 0) && ($v !== 0.0)) && ($v !== '') && (is_array($v) ? (count($v) > 0) : true);
 }
if (!class_exists("LS")) {
class LS {
    public function __construct($str, $escape = false) {
        $this->string = $escape ? (($escape === 'encq') ? static::encq(static::$jsContext, $str) : static::enc(static::$jsContext, $str)) : $str;
    }
    public function __toString() {
        return $this->string;
    }
    public static function escapeTemplate($template) {
        return addcslashes(addcslashes($template, '\\'), "'");
    }
    public static function raw($cx, $v) {
        if ($v === true) {
            if ($cx['flags']['jstrue']) {
                return 'true';
            }
        }

        if (($v === false)) {
            if ($cx['flags']['jstrue']) {
                return 'false';
            }
        }

        if (is_array($v)) {
            if ($cx['flags']['jsobj']) {
                if (count(array_diff_key($v, array_keys(array_keys($v)))) > 0) {
                    return '[object Object]';
                } else {
                    $ret = array();
                    foreach ($v as $k => $vv) {
                        $ret[] = static::raw($cx, $vv);
                    }
                    return join(',', $ret);
                }
            } else {
                return 'Array';
            }
        }

        return "$v";
    }
    public static function enc($cx, $var) {
        return htmlentities(static::raw($cx, $var), ENT_QUOTES, 'UTF-8');
    }
    public static function encq($cx, $var) {
        return str_replace(array('=', '`', '&#039;'), array('&#x3D;', '&#x60;', '&#x27;'), htmlentities(static::raw($cx, $var), ENT_QUOTES, 'UTF-8'));
    }
}
}
return function ($in = null, $options = null) {
    $helpers = array();
    $partials = array();
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => false,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'echo' => true,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' =>  array(
            'DEBUG_ERROR_LOG' => 1,
            'DEBUG_ERROR_EXCEPTION' => 2,
            'DEBUG_TAGS' => 4,
            'DEBUG_TAGS_ANSI' => 12,
            'DEBUG_TAGS_HTML' => 20,
        ),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    ob_start();echo '<dl>
  <dt>a. 12am:</dt>
  <dd>';if (lcr5e74eae2b6133ifvar($cx, ((is_array($in['realfeels']['0']) && isset($in['realfeels']['0']['hour_00'])) ? $in['realfeels']['0']['hour_00'] : null), false)){echo ' ',htmlentities((string)((is_array($in['realfeels']['0']) && isset($in['realfeels']['0']['temp___'])) ? $in['realfeels']['0']['temp___'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['0']) && isset($in['realfeels']['0']['winds'])) ? $in['realfeels']['0']['winds'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['0']) && isset($in['realfeels']['0']['humidity'])) ? $in['realfeels']['0']['humidity'] : null), ENT_QUOTES, 'UTF-8'),' ';}else{echo ' 0 ';}echo '</dd>
  <br>
    
  <dt>b. 6am:</dt>
  <dd>';if (lcr5e74eae2b6133ifvar($cx, ((is_array($in['realfeels']['6']) && isset($in['realfeels']['6']['hour_06'])) ? $in['realfeels']['6']['hour_06'] : null), false)){echo ' ',htmlentities((string)((is_array($in['realfeels']['6']) && isset($in['realfeels']['6']['temp___'])) ? $in['realfeels']['6']['temp___'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['6']) && isset($in['realfeels']['6']['winds'])) ? $in['realfeels']['6']['winds'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['6']) && isset($in['realfeels']['6']['humidity'])) ? $in['realfeels']['6']['humidity'] : null), ENT_QUOTES, 'UTF-8'),' ';}else{echo ' 0 ';}echo '</dd>
  <br>
  
  <dt>c. 1pm:</dt>
  <dd>';if (lcr5e74eae2b6133ifvar($cx, ((is_array($in['realfeels']['13']) && isset($in['realfeels']['13']['hour_13'])) ? $in['realfeels']['13']['hour_13'] : null), false)){echo ' ',htmlentities((string)((is_array($in['realfeels']['13']) && isset($in['realfeels']['13']['temp___'])) ? $in['realfeels']['13']['temp___'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['13']) && isset($in['realfeels']['13']['winds'])) ? $in['realfeels']['13']['winds'] : null), ENT_QUOTES, 'UTF-8'),' / ',htmlentities((string)((is_array($in['realfeels']['13']) && isset($in['realfeels']['13']['humidity'])) ? $in['realfeels']['13']['humidity'] : null), ENT_QUOTES, 'UTF-8'),' ';}else{echo ' 0 ';}echo '</dd>
  <br>
</dl>';return ob_get_clean();
};?>