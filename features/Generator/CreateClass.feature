Feature: generator create-class
  It should create new classes.

Scenario: Create a model class
  Given file "library/CMTools/Module/Foo.php" does not exist
  When I run "scripts/cm.php generator create-class CMTools_Module_Foo"
  Then file "library/CMTools/Module/Foo.php" exists

Scenario: Create a class
  Given file "library/CMTools/Foo/Bar.php" does not exist
  When I run "scripts/cm.php generator create-class CMTools_Foo_Bar"
  Then file "library/CMTools/Foo/Bar.php" exists

Scenario: Create a view
  Given file "library/CMTools/Foo/Bar.js" does not exist
  And file "library/CMTools/Foo/Bar.php" does not exist
  And file "layout/default/Foo/Bar/default.less" does not exist
  And file "layout/default/Foo/Bar/default.tpl" does not exist
  When I run "scripts/cm.php generator create-view CMTools_Foo_Bar"
  Then file "library/CMTools/Foo/Bar.js" exists
  And file "library/CMTools/Foo/Bar.php" exists
  And file "layout/default/Foo/Bar/default.less" exists
  And file "layout/default/Foo/Bar/default.tpl" exists
