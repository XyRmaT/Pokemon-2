<?php
function parse_template($file, $templateid, $tpldir) {

    $nest = 5;
    $tplfile = ROOT . '/' . $tpldir . '/' . $file . '.php';
    $objfile = ROOT_CACHE . '/template/tpl_' . $templateid . '_' . str_replace('/', '_', $file) . '.php';

    if(!@$fp = fopen($tplfile, 'r')) {
        exit("Current template file './$tpldir/$file.htm' not found or have no access!");
    }
    $template = fread($fp, filesize($tplfile));
    fclose($fp);

    $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
    $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

    //$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
    $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}\n", $template);
    $template = preg_replace("/\{lang\s+(.+?)\}/ies", "languagevar('\\1')", $template);
    $template = preg_replace("/\{faq\s+(.+?)\}/ies", "faqvar('\\1')", $template);
    $template = str_replace("{LF}", "<?=\"\\n\"?>\n", $template);

    $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
    $template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
    $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);

    $template = "\n$template";
    $template = preg_replace("/\{template\s+([a-z0-9_]+)\}/is", "\n<? include template('\\1'); ?>\n", $template);
    $template = preg_replace("/\{template\s+(.+?)\}/is", "\n<? include template(\\1); ?>\n", $template);
    $template = preg_replace("/\{css\s+(\S+)}/is", "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"<? echo CC::css(\\1); ?>\">\n", $template);
    $template = preg_replace("/\{css\s+(\S+)\s+(\S+)\}/is", "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"<? echo CC::css(\\1, \\2); ?>\">\n", $template);
    $template = preg_replace("/\{eval\s+(.+?)\}/ies", "stripvtags('\n<? \\1 ?>\n','')", $template);
    $template = preg_replace("/\{echo\s+(.+?)\}/ies", "stripvtags('\n<? echo \\1; ?>\n','')", $template);
    $template = preg_replace("/\{elseif\s+(.+?)\}/ies", "stripvtags('\n<? } elseif(\\1) { ?>\n','')", $template);
    $template = preg_replace("/\{else\}/is", "\n<? } else { ?>\n", $template);

    for($i = 0; $i < $nest; $i++) {
        $template = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}/ies", "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')", $template);
        $template = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}*/ies", "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')", $template);
        $template = preg_replace("/\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}/ies", "stripvtags('<? if(\\1) { ?>','\\2<? } ?>')", $template);
    }

    $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
    $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

    if(!@$fp = fopen($objfile, 'w')) {
        exit("Directory not found or have no access!");
    }

    $template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "transamp('\\0')", $template);
    $template = preg_replace("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/ise", "stripscriptamp('\\1')", $template);

    $template = preg_replace("/\n+\t*\n+/", "", $template);

    flock($fp, 2);
    fwrite($fp, $template . "\n");
    fclose($fp);
}

function transamp($str) {
    $str = str_replace('&', '&amp;', $str);
    $str = str_replace('&amp;amp;', '&amp;', $str);
    $str = str_replace('\"', '"', $str);
    return $str;
}

function addquote($var) {
    return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function languagevar($var) {
    if(isset($GLOBALS['language'][$var])) {
        return $GLOBALS['language'][$var];
    } else {
        return "!$var!";
    }
}

function stripvtags($expr, $statement) {
    $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
    $statement = str_replace("\\\"", "\"", $statement);
    return $expr.$statement;
}

function stripscriptamp($s) {
    $s = str_replace('&amp;', '&', $s);
    return "<script src=\"$s\" type=\"text/javascript\"></script>";
}

function template($file, $templateid = 0, $tpldir = '') {

    $tplrefresh = 1;
    $tpldir     = $tpldir ? $tpldir : (defined('ROOT_TEMPLATE') ? ROOT_TEMPLATE : 'source-tpl');
    $tplfile    = ROOT . '/' . $tpldir . '/' . $file . '.php';
    $objfile    = ROOT_CACHE . '/template/tpl_' . $templateid . '_' . str_replace('/', '_', $file) . '.php';

    //if(!file_exists($tplfile)) return FALSE;

    if($tplrefresh === 1 || ($tplrefresh > 1 && substr($GLOBALS['timestamp'], -1) > $tplrefresh)) {
        $objfiletime = @filemtime($objfile);
        if(@filemtime($tplfile) > ($objfiletime === FALSE ? 0 : $objfiletime)) {
            parse_template($file, $templateid, $tpldir);
        }
    }

    return $objfile;

}