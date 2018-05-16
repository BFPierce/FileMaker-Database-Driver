# FileMaker-Database-Wrapper

- Base class that wraps common functionality for interacting with a FileMaker Database.

```php
/**
 * Example usage.
 */
require_once('FMDatabase.php');

class MyExampleEndpoint extends FMDatabase {

    private $layout = "MyExampleLayout";

    public function __construct() {
        parent::__construct();
    }

    public function processRecord($record) {

        if ($record == null)
            return false;

        /* Potential preprocessing or other logic */

        return $this->AddRecord($layout, $record);
    }

}

$endpoint = new MyExampleEndpoint();

$sampleJSON = '{"firstName": Test, "lastName": Testerson, "age": 36}';
$decoded = json_decode($sampleJSON, true);

echo $endpoint->processRecord($decoded);


```
