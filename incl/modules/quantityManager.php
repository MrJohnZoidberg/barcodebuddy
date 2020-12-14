<?php

class QuantityManager {

    /**
     * Gets an array of locally stored quantities
     * @return Quantity[]
     * @throws DbConnectionDuringEstablishException
     */
    public static function getQuantities(): array {
        $db       = DatabaseConnection::getInstance()->getDatabaseReference();
        $res      = $db->query('SELECT * FROM Quantities');
        $barcodes = array();
        while ($row = $res->fetchArray()) {
            array_push($barcodes, new Quantity($row));
        }
        return $barcodes;
    }


    /**
     * Gets quantity for stored barcode quantities or 1 as default
     *
     * @param string $barcode
     * @return int quantity or 1 if not found
     * @throws DbConnectionDuringEstablishException
     */
    public static function getQuantityForBarcode(string $barcode): int {
        $db  = DatabaseConnection::getInstance()->getDatabaseReference();
        $res = $db->query("SELECT * FROM Quantities WHERE barcode='$barcode'");
        if ($row = $res->fetchArray()) {
            return (new Quantity($row))->quantity;
        } else {
            return 1;
        }
    }

    /**
     * Save product name if already stored as Quantity
     *
     * @param $barcode
     * @param $productname
     */
    public static function refreshProductName($barcode, $productname) {
        $db  = DatabaseConnection::getInstance()->getDatabaseReference();
        $res = $db->query("SELECT * FROM Quantities WHERE barcode='$barcode'");
        if ($row = $res->fetchArray()) {
            $db->exec("UPDATE Quantities SET product='$productname' WHERE barcode='$barcode'");
        }
    }


    /**
     * Adds a default quantity for a barcode or updates the product
     *
     * @param $barcode
     * @param $amount
     * @param null $product
     */
    public static function addUpdateEntry($barcode, $amount, $product = null) {
        checkIfNumeric($amount);
        $db = DatabaseConnection::getInstance()->getDatabaseReference();
        if ($product == null) {
            $db->exec("REPLACE INTO Quantities(barcode, quantity) VALUES ('$barcode', $amount)");
        } else {
            $db->exec("REPLACE INTO Quantities(barcode, quantity, product) VALUES ('$barcode', $amount, '$product')");
        }
    }


    /**
     * Deletes Quantity barcode
     * @param $id
     * @throws DbConnectionDuringEstablishException
     */
    public static function delete($id) {
        checkIfNumeric($id);
        $db = DatabaseConnection::getInstance()->getDatabaseReference();
        $db->exec("DELETE FROM Quantities WHERE id='$id'");
    }
}

class Quantity {

    public $id;
    public $barcode;
    public $quantity;
    public $product;


    public function __construct($dbRow) {
        if (!$this->isValidRow($dbRow)) {
            throw new RuntimeException("Invalid row supplied to create Quantity Object");
        }
        $this->id       = $dbRow['id'];
        $this->barcode  = $dbRow['barcode'];
        $this->quantity = $dbRow['quantity'];
        $this->product  = $dbRow['product'];
    }

    private function isValidRow($dbRow): bool {
        return (array_key_exists('id', $dbRow) &&
            array_key_exists('barcode', $dbRow) &&
            array_key_exists('quantity', $dbRow) &&
            array_key_exists('product', $dbRow));
    }
}