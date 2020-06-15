<?php

final class DB {
  // required by subject
  private static string $DB_DSN;
  private static string $DB_USER;
  private static string $DB_PASSWORD;

  // https://phpdelusions.net/pdo
  private static Array $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  private static ?PDO $instance = null;

  /**
   * @return PDO
   * @throws Exception in case of unset variables from ./config/database.php
   * @throws PDOException in case of PDO error
   */
  public static function get(): PDO
  {
    if (static::$instance === null) {
      require_once __DIR__ . '/../config/database.php';

      if (empty($DB_DSN) || empty($DB_USER) || empty($DB_PASSWORD)) {
        throw new Exception('You have malformed ./config/database.php');
      }

      self::$DB_DSN = $DB_DSN;
      self::$DB_USER = $DB_USER;
      self::$DB_PASSWORD = $DB_PASSWORD;

      try {
        self::$instance = new PDO(self::$DB_DSN, self::$DB_USER, self::$DB_PASSWORD, self::$options);
      } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
      }
      self::checkIntegrity();
    }

    return static::$instance;
  }

  private static function checkIntegrity(): void
  {
    try {
      self::$instance->query('SELECT 1 FROM `images`');
    } catch (\PDOException $e) {
      require_once __DIR__ . '/../config/setup.php';
    }
  }

  /**
   * is not allowed to call from outside to prevent from creating multiple instances,
   * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
   */
  private function __construct() {}

  /**
   * prevent the instance from being cloned (which would create a second instance of it)
   */
  private function __clone() {}

  /**
   * prevent from being unserialized (which would create a second instance of it)
   */
  private function __wakeup() {}
}
