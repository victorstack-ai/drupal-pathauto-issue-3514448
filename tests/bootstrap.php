<?php

/**
 * @file
 * Test bootstrap file.
 *
 * This file is discovered by the PHING_TEST_BOOTSTRAP_FILE environment
 * variable.
 */

use Drupal\Core\Test\Bootstrap\DrupalTestKernel;

// If the user did not provide a test bootstrap file, use the default.
// The path is relative to the directory for which Phing is being run.
$file = getenv('PHING_TEST_BOOTSTRAP_FILE')
  ?: 'vendor/drupal/core/tests/bootstrap.php';

// If the bootstrap file does not exist, we can't proceed.
if (!file_exists($file) || !is_readable($file)) {
  fwrite(STDERR, "The test bootstrap file '$file' is not a readable file.
");
  exit(1);
}

// Set the test kernel class.
//
// This is used by Drupal's test runner to decide which kernel to use.
//
// @see \Drupal\Core\Test\TestRunner::prepareEnvironment()
putenv('DRUPAL_TEST_KERNEL_CLASS=' . DrupalTestKernel::class);

// Include the file.
require_once $file;
