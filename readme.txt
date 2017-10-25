=== BBSpoiler ===
Contributors: Klemens
Donate link: 
Tags: rpg aventurien solo adventures
Requires at least: 3.9
Tested up to: 4.8.0
Stable tag: trunk

This plugin allows you to display twine text adventures in your wordpress blog using the shortcodes:

[aventurien-solo module="example"][/aventurien-solo]

== Description ==

You can use this plugin to display twine text adventures.

The text adventures have to be placed into the modules subfolder of the plugin and have to be in html format.
To generate such a html file, create the text adventure with twine2 with the harlowe engine and export it as html.
The following harlowe markups are supported:

    ''Bold'', //italics//, ^^superscript^^, ~~strikethrough~~, and <p>HTML tags</p>
    Links to other passages in the formats
    [[link text->passage name]]
    [[passage name<-link text]]
    [[passage name]]

The following harlowe macros are supported:

(set:$mytext as "open")
(set:$mynumber as 4711)
(if:$mytext == "open")The door is open.(endif:)
(if:$mynumber >= 5)You've got $mynumber pieces.(else:)You've not found anything yet.(endif:)

Note: You can display the value of a variable simply by using it in the passage text.

For example:

`
[aventurien-solo module="example"][/aventurien-solo]
`

== Installation ==

1. Upload <strong>aventurien-solo</strong> folder to the <strong>/wp-content/plugins/</strong> directory.
2. Activate the plugin through the <strong>Plugins</strong> menu in WordPress.
3. That's all.

== Changelog ==

= 1.00 =
* first version
