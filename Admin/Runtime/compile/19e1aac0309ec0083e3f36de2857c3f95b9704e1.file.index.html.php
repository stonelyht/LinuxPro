<?php /* Smarty version Smarty-3.1.14, created on 2018-09-23 18:06:54
         compiled from "E:\wamp64\www\LinuxPro\Admin\Views\index.html" */ ?>
<?php /*%%SmartyHeaderCode:54345ba76549352dc9-20076317%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '19e1aac0309ec0083e3f36de2857c3f95b9704e1' => 
    array (
      0 => 'E:\\wamp64\\www\\LinuxPro\\Admin\\Views\\index.html',
      1 => 1537697213,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '54345ba76549352dc9-20076317',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_5ba76549368898_71005389',
  'variables' => 
  array (
    'menu' => 0,
    'content' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ba76549368898_71005389')) {function content_5ba76549368898_71005389($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('./header.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<div class="view-body">
    <?php echo $_smarty_tpl->tpl_vars['menu']->value;?>


    <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

</div>

<?php echo $_smarty_tpl->getSubTemplate ('./footer.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>