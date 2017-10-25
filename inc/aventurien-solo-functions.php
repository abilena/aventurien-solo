<?php

require_once('../../../wp-load.php');
require_once('aventurien-solo-database.php');
require_once('template.class.php');

$pid = 1;
$vars = array();

function aventurien_solo_display($module, $title, $last_pid, $passage, $debug)
{
    global $vars;
    $user = wp_get_current_user()->user_login;
    $path_local = plugin_dir_path(__FILE__);
    $module_file = $path_local . "../modules/" . $module . ".html";
    if (!file_exists($module_file))
    {
        $template = new AventurienSolo\Template($path_local . "../tpl/page.html");
        $template->set("Name", "Fehler");
        $template->set("Content", "Das Abenteuer <i>$title</i> konnte nicht gefunden werden.");
        $template->set("Menu", "");
        return $template->output();
    }

    $xml = aventurien_solo_extract_harlowe_xml($module_file);

    if ($passage != "")
    {
        // load requested passage and use it's pid
        $passage_xml = $xml->xpath("/tw/tw-storydata/tw-passagedata[@name=\"$passage\"]")[0];
        $pid = (int)$passage_xml->attributes()['pid'];
    }
    else
    {
        // query the last seen pid and load that passage
        $pid = aventurien_solo_db_get_pid($module, $user);
        $passage_xml = $xml->xpath("/tw/tw-storydata/tw-passagedata[@pid=$pid]")[0];
    }

    if ($passage == "Start")
    {
        $vars = array();
    }
    else
    {
        $vars = aventurien_solo_db_get_vars($module, $user);
    }

    $passage_name = $passage_xml->attributes()['name'];
    $passage_text = $passage_xml[0];

    $passage_text = aventurien_solo_do_replacements($passage_text);
    $passage_text = aventurien_solo_command_text($passage_text);
    $passage_text = aventurien_solo_command_set($passage_text, ((!$last_pid) || ($last_pid == $pid)));
    $passage_text = aventurien_solo_command_if($passage_text);
    $passage_text = aventurien_solo_var_replace($passage_text, "");
    $passage_text = nl2br($passage_text);

    aventurien_solo_db_set_pid($module, $user, $pid);
    aventurien_solo_db_set_vars($module, $user, $vars);

    $debug_html = "";
    foreach ($vars as $var_name => $var_value)
    {
        $template_debug = new AventurienSolo\Template($path_local . "../tpl/debug.html");
        $template_debug->set("Name", $var_name);
        $template_debug->set("Value", $var_value);
        $debug_html .= $template_debug->output();
    }

    $template_menu = new AventurienSolo\Template($path_local . "../tpl/menu.html");
    $template = new AventurienSolo\Template($path_local . "../tpl/page.html");
    $template->set("Name", $passage_name);
    $template->set("Content", $passage_text);
    $template->set("Menu", $template_menu->output());
    $template->set("Debug", $debug_html);
    $template->set("DebugDisplay", ($debug == 'true') ? "block" : "none");
    $output = $template->output();

    return $output;
}

function aventurien_solo_command_text($passage_text)
{
    $passage_text = preg_replace('/\(text\:\s*(.*?)\s*\)/', '"$1"', $passage_text);

    return $passage_text;
}

function aventurien_solo_get_var($variable)
{
    global $vars;

    if (array_key_exists($variable, $vars))
    {
        return $vars[$variable];
    }
    else
    {
        return NULL;
    }
}

function aventurien_solo_var_replace($expression, $quotes)
{
    preg_match_all('/\$(\w*)/', $expression, $matches);

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) 
    {
        $variable = $matches[1][$i];
        $value = aventurien_solo_get_var($variable);

        if (!is_numeric($value))
        {
            $value = $quotes . $value . $quotes;
        }

        $expression = str_replace("\$" . $variable, $value, $expression);
    }

    return $expression;
}

function aventurien_solo_command_set($passage_text, $reload)
{
    if (!$reload)
    {
        preg_match_all('/\(set\:\s*\$(\w*)\s* to \s*(.*?)\s*\)/', $passage_text, $matches);

        global $vars;
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) 
        {
            $variable = $matches[1][$i];
            $expression = $matches[2][$i];

            // replace all known variables in expression by their values so they can be evaled
            $expression = "\$vars['$variable'] = " . aventurien_solo_var_replace($expression, "'") . ";";
            // evaluate the set statement
            eval($expression);
        }
    }

    // remove all set statements from the text
    $passage_text = preg_replace('/\(set\:(.*?)\)/', '', $passage_text);

    return $passage_text;
}

function aventurien_solo_command_if($passage_text)
{
    preg_match_all('/\(if\:\s*(.*?)\s*\)(.*?)\(endif\:\)/', $passage_text, $matches);

    global $vars;

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) 
    {
        $text = $matches[0][$i];
        $condition = $matches[1][$i];
        $content = $matches[2][$i];
        $if_text = $content;
        $else_text = "";
        $result = "";

        if (preg_match('/(.*?)\(else\:\)(.*)/', $content, $content_matches))
        {
            $if_text = $content_matches[1];
            $else_text = $content_matches[2];
        }

        // replace all known variables in expression by their values so they can be evaled
        $condition = "\$result = " . aventurien_solo_var_replace($condition, "'") . ";";
        // evaluate the set statement
        eval($condition);

        if ($result)
        {
            $passage_text = str_replace($text, $if_text, $passage_text);
        }
        else
        {
            $passage_text = str_replace($text, $else_text, $passage_text);
        }
    }

    return $passage_text;
}

function aventurien_solo_do_replacements($passage_text)
{
    global $pid;
    $passage_text = preg_replace('/\[\[(.*?)\-\>(.*?)\]\]/', '<a href="#" onclick="javascript:select(' . $pid . ', \'$2\')">$1</a>', $passage_text);
    $passage_text = preg_replace('/\[\[(.*?)\<\-(.*?)\]\]/', '<a href="#" onclick="javascript:select(' . $pid . ', \'$1\')">$2</a>', $passage_text);
    $passage_text = preg_replace('/\[\[(.*?)\]\]/', '<a href="#" onclick="javascript:select(' . $pid . ', \'$1\')">$1</a>', $passage_text);
    $passage_text = preg_replace('/\[\[(.*?)\]\]/', '<a href="#" onclick="javascript:select(' . $pid . ', \'$1\')">$1</a>', $passage_text);
    $passage_text = preg_replace('/\'\'(.*?)\'\'/', '<strong>$1</strong>', $passage_text);
    $passage_text = preg_replace('/\/\/(.*?)\/\//', '<i>$1</i>', $passage_text);
    $passage_text = preg_replace('/\^\^(.*?)\^\^/', '<sup>$1</sup>', $passage_text);
    $passage_text = preg_replace('/\~\~(.*?)\~\~/', '<del>$1</del>', $passage_text);

    return $passage_text;
}

function aventurien_solo_extract_harlowe_xml($file)
{
    $html = file_get_contents($file);
    $start = strpos($html, "<tw-story");
    $end = strrpos($html, "</tw-storydata>") + 15;

    $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\r\n";
    $xml .= '<tw>' . "\r\n";
    $xml .= substr($html, $start, $end - $start);
    $xml .= "</tw>";
    $xml = str_replace("hidden>", ">", $xml);

    return simplexml_load_string($xml);
}

?>