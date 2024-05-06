<?php

/**
 * 'format_image_path'  格式化图片路径
 * 'web_route'  网站路由
 * 'img_crop'  图片压缩
 * 'mb_str_replace'  字符串替换
 * 'site_seo'  站点SEO
 * 'format_date'  格式化日期
 * 'mb_trim'  mb_trim
 * 'mb_ltrim'  mb_ltrim
 * 'mb_rtrim'  mb_rtrim
 */

if (!function_exists('format_image_path')) {
    /**
     * 格式化图片路径
     * 1. 如果是绝对路径，则直接返回
     * 2. 如果是相对路径，则加上前缀
     * @param string $path
     *                      图片路径
     *                      例如：/uploads/2018/01/01/1.jpg
     *                      例如：http://www.baidu.com/1.jpg
     *                      例如：1.jpg
     *                      例如：uploads/2018/01/01/1.jpg
     *                      例如：http://www.baidu.com/uploads/2018/01/01/1.jpg
     *                      例如：http://www.baidu.com/uploads/2018/01/01/1.jpg?x-oss-process=image/resize,m_fill,w_100,h_100
     * @param string $prefix
     *                      图片路径前缀
     *                      例如：http://www.baidu.com
     *                      例如：http://www.baidu.com/uploads
     *                      例如：http://www.baidu.com/uploads/2018/01/01
     * @return string
     */
    function format_image_path($path, $prefix = '')
    {
        if (empty($path)) {
            return '';
        }
        if (empty($prefix)) {
            // cdn
            $prefix = config('filesystems.disks.admin.cdn');
        }

        $prefix = trim($prefix, '/');

        if (strpos($path, 'http') === 0) {
            return $path;
        }
        if (strpos($path, '/') === 0) {
            return $prefix . $path;
        }
        return $prefix . '/' . $path;
    }
}

if (!function_exists('web_route')) {
    /**
     * 网站路由
     * @param      $str
     * @param null $attrs
     * @return string
     */
    function web_route($str, $attrs = null)
    {
        return route(config('web.route.prefix') . '.' . $str, $attrs);
    }
}

if (!function_exists('img_crop')) {
    /**
     * 图片压缩
     * @param      $url
     * @param null $width
     * @param null $height
     * @return string
     */
    function img_crop($url, $width = null, $height = null)
    {
        if (!empty($width)) {
            $width = $width * 2;
        }
        if (!empty($height)) {
            $height = $height * 2;
        }
        return "{$url}?imageMogr2/thumbnail/!{$width}x{$height}r/interlace/0|imageMogr2/gravity/center/crop/{$width}x{$height}/auto-orient";
    }
}

if (!function_exists('mb_str_replace')) {
    /**
     * 字符串替换
     * Multibyte safe version of str_replace.
     * See http://php.net/manual/en/function.str-replace.php
     * @link   http://www.php.net/manual/en/ref.mbstring.php#107631
     * @author Michael Robinson <mike@pagesofinterest.net>
     * @author Lee Byron
     */
    function mb_str_replace($search, $replace, $subject, $encoding = 'utf8', &$count = null)
    {
        if (is_array($subject)) {
            $result = array();
            foreach ($subject as $item) {
                $result[] = mb_str_replace($search, $replace, $item, $encoding, $count);
            }
            return $result;
        }

        if (!is_array($search)) {
            return _mb_str_replace($search, $replace, $subject, $encoding, $count);
        }

        $replace_is_array = is_array($replace);
        foreach ($search as $key => $value) {
            $subject = _mb_str_replace($value, $replace_is_array ? $replace[$key] : $replace, $subject, $encoding, $count);
        }
        return $subject;
    }

    /**
     * Implementation of mb_str_replace. Do not call directly.
     */
    function _mb_str_replace($search, $replace, $subject, $encoding, &$count = null)
    {
        $search_length = mb_strlen($search, $encoding);
        $subject_length = mb_strlen($subject, $encoding);
        $offset = 0;
        $result = '';

        while ($offset < $subject_length) {
            $match = mb_strpos($subject, $search, $offset, $encoding);
            if ($match === false) {
                if ($offset === 0) {
                    // No match was ever found, just return the subject.
                    return $subject;
                }
                // Append the final portion of the subject to the replaced.
                $result .= mb_substr($subject, $offset, $subject_length - $offset, $encoding);
                break;
            }
            if ($count !== null) {
                $count++;
            }
            $result .= mb_substr($subject, $offset, $match - $offset, $encoding);
            $result .= $replace;
            $offset = $match + $search_length;
        }
        return $result;
    }
}

if (!function_exists('site_seo')) {
    /**
     * 站点SEO
     * @param $model model
     * @param $name  meta name="title" 标题
     * @return string|void
     */
    function site_seo($model, $name)
    {
        // 模型标题
        $modeTitle = '';
        if (!empty($model->title)) {
            $modeTitle = $model->title;
        } else if (!empty($model->name)) {
            $modeTitle = $model->name;
        }
        // 模型关键字
        $modelKeywords = '';
        if (!empty($model->keywords)) {
            $modelKeywords = $model->keywords;
            if (is_array($modelKeywords)) {
                $modelKeywords = implode(',', $modelKeywords);
            }
        }
        // 模型描述
        $modelDescription = '';
        if (!empty($model->description)) {
            $modelDescription = $model->description;
        }

        // SEO标题、关键字、描述
        $siteSeoNameVal = '';
        if (!empty($model->siteSeo)) {
            $seoName = 'seo_' . $name;
            $siteSeoNameVal = $model->siteSeo->$seoName;
        }
        if (empty($siteSeoNameVal)) {
            $siteSeoNameVal = '{' . $name . '}';
        }

        $result = mb_str_replace(
            ['{title}', '{keywords}', '{description}'],
            [
                $modeTitle, $modelKeywords,
                $modelDescription
            ],
            $siteSeoNameVal
        );

        if (!empty($result)) {
            return $result;
        }
        return null;
    }
}

if (!function_exists('format_date')) {
    /**
     * 格式化日期
     * @param        $date
     * @param string $lang
     * @return string
     */
    function format_date($date, $lang = 'zh-TW')
    {
        if ('zh-TW' == $lang) {
            $week = ["週日", "週一", "週二", "週三", "週四", "週五", "週六"];
        } else {
            $week = ["周日", "周一", "周二", "周三", "周四", "周五", "周六"];
        }
        return date('m月d日', strtotime($date)) . ' ' . $week[date('w', strtotime($date))];
    }
}

if (extension_loaded('mbstring')) {
    /**
     * Make sure to set mb_iternal_encoding in your project!!
     * The defualt value of mb_internal_encoding is often ISO-8859-1.
     */
    #mb_internal_encoding('UTF-8');
    if (!function_exists('mb_trim')) {
        /**
         * mb_trim
         * Strip whitespace (or other characters) from the beginning and end of a string.
         * @param string $str      The string that will be trimmed.
         * @param string $charlist Optionally, the stripped characters can also be specified using the charlist parameter. Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
         * @param string $encoding The encoding parameter is the character encoding. If it is omitted, the internal character encoding value will be used.
         * @return string The trimmed string.
         */
        function mb_trim($str, $charlist = null, $encoding = null)
        {
            if ($encoding === null) {
                $encoding = mb_internal_encoding(); // Get internal encoding when not specified.
            }
            if ($charlist === null) {
                $charlist = "\\x{20}\\x{9}\\x{A}\\x{D}\\x{0}\\x{B}"; // Standard charlist, same as trim.
            } else {
                $chars = preg_split('//u', $charlist, -1, PREG_SPLIT_NO_EMPTY); // Splits the string into an array, character by character.
                foreach ($chars as $c => &$char) {
                    if (preg_match('/^\x{2E}$/u', $char) && preg_match('/^\x{2E}$/u', $chars[$c + 1])) { // Check for character ranges.
                        $ch1 = hexdec(substr($chars[$c - 1], 3, -1));
                        $ch2 = (int)substr(mb_encode_numericentity($chars[$c + 2], [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1);
                        $chs = '';
                        for ($i = $ch1; $i <= $ch2; $i++) { // Loop through characters in Unicode order.
                            $chs .= "\\x{" . dechex($i) . "}";
                        }
                        unset($chars[$c], $chars[$c + 1], $chars[$c + 2]); // Unset the now pointless values.
                        $chars[$c - 1] = $chs;                             // Set the range.
                    } else {
                        $char = "\\x{" . dechex(substr(mb_encode_numericentity($char, [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1)) . "}"; // Convert the character to it's unicode codepoint in \x{##} format.
                    }
                }
                $charlist = implode('', $chars); // Return the array to string type.
            }
            $pattern = '/(^[' . $charlist . ']+)|([' . $charlist . ']+$)/u'; // Define the pattern.
            return preg_replace($pattern, '', $str);                         // Return the trimmed value.
        }
    }
    if (!function_exists('mb_ltrim')) {
        /**
         * mb_ltrim
         * Strip whitespace (or other characters) from the beginning of a string.
         * @param string $str      The string that will be trimmed.
         * @param string $charlist Optionally, the stripped characters can also be specified using the charlist parameter. Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
         * @param string $encoding The encoding parameter is the character encoding. If it is omitted, the internal character encoding value will be used.
         * @return string The trimmed string.
         */
        function mb_ltrim($str, $charlist = null, $encoding = null)
        {
            if ($encoding === null) {
                $encoding = mb_internal_encoding(); // Get internal encoding when not specified.
            }
            if ($charlist === null) {
                $charlist = "\\x{20}\\x{9}\\x{A}\\x{D}\\x{0}\\x{B}"; // Standard charlist, same as trim.
            } else {
                $chars = preg_split('//u', $charlist, -1, PREG_SPLIT_NO_EMPTY); // Splits the string into an array, character by character.
                foreach ($chars as $c => &$char) {
                    if (preg_match('/^\x{2E}$/u', $char) && preg_match('/^\x{2E}$/u', $chars[$c + 1])) { // Check for character ranges.
                        $ch1 = hexdec(substr($chars[$c - 1], 3, -1));
                        $ch2 = (int)substr(mb_encode_numericentity($chars[$c + 2], [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1);
                        $chs = '';
                        for ($i = $ch1; $i <= $ch2; $i++) { // Loop through characters in Unicode order.
                            $chs .= "\\x{" . dechex($i) . "}";
                        }
                        unset($chars[$c], $chars[$c + 1], $chars[$c + 2]); // Unset the now pointless values.
                        $chars[$c - 1] = $chs;                             // Set the range.
                    } else {
                        $char = "\\x{" . dechex(substr(mb_encode_numericentity($char, [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1)) . "}"; // Convert the character to it's unicode codepoint in \x{##} format.
                    }
                }
                $charlist = implode('', $chars); // Return the array to string type.
            }
            $pattern = '/(^[' . $charlist . ']+)/u'; // Define the pattern.
            return preg_replace($pattern, '', $str); // Return the trimmed value.
        }
    }
    if (!function_exists('mb_rtrim')) {
        /**
         * mb_rtrim
         * Strip whitespace (or other characters) from the end of a string.
         * @param string $str      The string that will be trimmed.
         * @param string $charlist Optionally, the stripped characters can also be specified using the charlist parameter. Simply list all characters that you want to be stripped. With .. you can specify a range of characters.
         * @param string $encoding The encoding parameter is the character encoding. If it is omitted, the internal character encoding value will be used.
         * @return string The trimmed string.
         */
        function mb_rtrim($str, $charlist = null, $encoding = null)
        {
            if ($encoding === null) {
                $encoding = mb_internal_encoding(); // Get internal encoding when not specified.
            }
            if ($charlist === null) {
                $charlist = "\\x{20}\\x{9}\\x{A}\\x{D}\\x{0}\\x{B}"; // Standard charlist, same as trim.
            } else {
                $chars = preg_split('//u', $charlist, -1, PREG_SPLIT_NO_EMPTY); // Splits the string into an array, character by character.
                foreach ($chars as $c => &$char) {
                    if (preg_match('/^\x{2E}$/u', $char) && preg_match('/^\x{2E}$/u', $chars[$c + 1])) { // Check for character ranges.
                        $ch1 = hexdec(substr($chars[$c - 1], 3, -1));
                        $ch2 = (int)substr(mb_encode_numericentity($chars[$c + 2], [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1);
                        $chs = '';
                        for ($i = $ch1; $i <= $ch2; $i++) { // Loop through characters in Unicode order.
                            $chs .= "\\x{" . dechex($i) . "}";
                        }
                        unset($chars[$c], $chars[$c + 1], $chars[$c + 2]); // Unset the now pointless values.
                        $chars[$c - 1] = $chs;                             // Set the range.
                    } else {
                        $char = "\\x{" . dechex(substr(mb_encode_numericentity($char, [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1)) . "}"; // Convert the character to it's unicode codepoint in \x{##} format.
                    }
                }
                $charlist = implode('', $chars); // Return the array to string type.
            }
            $pattern = '/([' . $charlist . ']+$)/u'; // Define the pattern.
            return preg_replace($pattern, '', $str); // Return the trimmed value.
        }
    }
}

if (!function_exists('assetv')) {
    /**
     * assetv
     * Get the version of an asset file.
     * @param string $path The path to the asset file.
     * @return string The version of the asset file.
     */
    function assetv($path, $secure = null)
    {
        return app('url')->asset($path, $secure) . '?v=' . config('web.version');
    }
}

if (!function_exists('cache_param')) {
    /**
     * 格式化日期
     * @param $param
     * @return mixed
     */
    function cache_param($param)
    {
        return Cache::get($param);
    }
}

if (!function_exists('asset_oss')) {
    /**
     * cos 资源文件加载
     * @param $path
     * @return string
     */
    function asset_oss($path)
    {
        return trim(config('filesystems.disks.admin.cdn'), '/') . '/' . trim($path, '/');
    }
}

if (!function_exists('asset_cn')) {
    /**
     * 中文站 cos 资源文件加载 lanshauk-cn
     * @param $path
     * @return string
     */
    function asset_cn($path)
    {
        return trim(config('filesystems.disks.admin.cdn'), '/') . '/lanshauk-cn/' . trim($path, '/');
    }
}

if (!function_exists('pages_reduce')) {
    function pages_reduce($elements)
    {
        if (count($elements) == 3) {    // 包含...
            $index = 0;
            foreach ($elements as $key => $element) {
                $index++;
                if ($index == 1) {    // ...前
                    if (count($element) > 3) {
                        $elements[$key] = array_slice($element, 0, 3);
                    }
                } else if ($index == 3) {   // ...后
                    $links = [];
                    foreach (array_reverse(array_slice(array_reverse($element), 0, 3)) as $link) {
                        $page = explode('page=', $link)[1];
                        $links[$page] = $link;
                    }
                    $elements[$key] = $links;
                }
            }
        }
        return $elements;
    }
}
