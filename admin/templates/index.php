<?
if ($_USER->isLoggedIn())
{
  foreach (AdminManager::$sections as $section)
  {
    if ($section->isAvailable())
    {
      redirect($section->getURL());
      break;
    }
  }
}
else
  displayAdminLogin($request);
?>