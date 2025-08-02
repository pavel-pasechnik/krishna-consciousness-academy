

<?php
// Проверка, что удаление вызвано WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Удалить мета-поля 'locale' у всех пользователей
$users = get_users([
  'fields' => 'ID'
]);
foreach ($users as $user_id) {
  delete_user_meta($user_id, 'locale');
}
