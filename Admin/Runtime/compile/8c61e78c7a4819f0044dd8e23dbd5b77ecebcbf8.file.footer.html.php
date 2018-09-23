<?php /* Smarty version Smarty-3.1.14, created on 2018-09-23 18:04:57
         compiled from "E:\wamp64\www\LinuxPro\Admin\Views\footer.html" */ ?>
<?php /*%%SmartyHeaderCode:121045ba7654937a900-89263983%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8c61e78c7a4819f0044dd8e23dbd5b77ecebcbf8' => 
    array (
      0 => 'E:\\wamp64\\www\\LinuxPro\\Admin\\Views\\footer.html',
      1 => 1537697095,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '121045ba7654937a900-89263983',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_5ba7654937c9e8_10588146',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ba7654937c9e8_10588146')) {function content_5ba7654937c9e8_10588146($_smarty_tpl) {?><script>
    $(".sidebar-title").live('click', function() {
        if ($(this).parent(".sidebar-nav").hasClass("sidebar-nav-fold")) {
            $(this).next().slideDown(200);
            $(this).parent(".sidebar-nav").removeClass("sidebar-nav-fold");
        } else {
            $(this).next().slideUp(200);
            $(this).parent(".sidebar-nav").addClass("sidebar-nav-fold");
        }
    });
</script>
</body>
</html><?php }} ?>