first go to wp-admin/edit-comments.php.
add the sentence(below) after $wp_list_talbe->prepare_items(search your text-editor search engine)

apply_filters('personal_comment_filter',$wp_list_table);

For help see the screenshot.png in details folder

i have included an edited-comments.php in details folder and the desired sentence is in line number 109 only for reference
if you are smart enough then just replace the edited-comments.php with yours one(not recommended)

Activate your plugin and maintain privacy.....

please veryfy against more than 4 authors


In Order to make your posts private
