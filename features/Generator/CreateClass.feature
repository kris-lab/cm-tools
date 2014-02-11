Feature: generator create-class
  It should create new classes.

Scenario: Create a model class
  Given file "library/CMTools/Model/Foo.php" does not exist
  When I run "scripts/cm.php generator create-class CMTools_Model_Foo"
  Then file "library/CMTools/Model/Foo.php" exists

