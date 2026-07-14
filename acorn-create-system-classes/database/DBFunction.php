<?php namespace Acorn\CreateSystem\Database;

use Exception;
use Acorn\CreateSystem\Util\Str;

class DBFunction {
    // TODO: Use this class
    protected static $functions = array();

    protected $db;

    public $schema;
    public $name; // Un-qualified, see schema 
    public $order;
    public $oid;
    public $parameters; // array
    public $returnType;

    public $comment;
    public $parsedComment; // array

    public static function fromRow(DB &$db, array $row)
    {
        return new self($db, ...$row);
    }

    public static function &get(string $name, string $schema = NULL): DBFunction
    {
        // Allow search with or without schema, with or without dot notation
        // Note that the Lojistiks system uses 2 schemas: public and product
        if ($schema) {
            $qualifiedName = "$schema.$name";
        } else {
            $nameParts     = explode('.', $name);
            $functionName  = (count($nameParts) == 2 ? $nameParts[1] : $nameParts[0]);
            $schemaName    = (count($nameParts) == 2 ? $nameParts[0] : 'public');
            $qualifiedName = "$schemaName.$functionName";
        }
        if (!isset(self::$tables[$qualifiedName]))
            throw new Exception("Function [$qualifiedName] not in static list");
        return self::$functions[$qualifiedName];
    }

    public function getSchema(): Schema
    {
        return Schema::get($this->schema);
    }

    protected function __construct(DB &$db, ...$properties)
    {
        $this->db = &$db;
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) $this->$name = $value;
        }
        $this->parsedComment = \Spyc::YAMLLoadString($this->comment);
        $previousName = NULL;
        foreach ($this->parsedComment as $name => $value) {
            $nameCamel = Str::camel($name);
            if (!property_exists($this, $nameCamel)) {
                $valueExport = var_export($value, TRUE);
                self::blockingAlert("Property [$nameCamel] does not exist on [$this->name] after [$previousName] with value [$valueExport]");
            }
            if (!isset($this->$nameCamel)) $this->$nameCamel = $value;
            $previousName = $name;
        }

        // Name and registration
        $nameParts  = explode('.', $this->name);
        if (count($nameParts) > 1)
            throw new Exception("Name [$this->name] is already qualified. Use schema instead");
        $qualifiedName = "$this->schema.$this->name";
        self::$functions[$qualifiedName] = $this;
    }

    static protected function blockingAlert(string $message, string $level = 'WARNING'): void
    {
        global $YELLOW, $NC;

        print("$YELLOW$level$NC: $message. Continue (y)? ");
        $yn = readline();
        if (strtolower($yn) == 'n') exit(0);
    }
}
