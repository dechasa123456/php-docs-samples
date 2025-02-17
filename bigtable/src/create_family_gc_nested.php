<?php

/**
 * Copyright 2019 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

// [START bigtable_create_family_gc_nested]
use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Protobuf\Duration;

/**
 * Create a new column family with a nested GC rule
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance where the table resides
 * @param string $tableId The ID of the table in which the rule needs to be created
 */
function create_family_gc_nested(
    string $projectId,
    string $instanceId,
    string $tableId
): void {
    $tableAdminClient = new BigtableTableAdminClient();

    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

    print('Creating column family cf5 with a Nested GC rule...' . PHP_EOL);
    // Create a column family with nested GC policies.
    // Create a nested GC rule:
    // Drop cells that are either older than the 10 recent versions
    // OR
    // Drop cells that are older than a month AND older than the
    // 2 recent versions
    $columnFamily5 = new ColumnFamily();
    $rule1 = (new GcRule())->setMaxNumVersions(10);

    $rule2Intersection = new GcRuleIntersection();
    $rule2Duration1 = new Duration();
    $rule2Duration1->setSeconds(3600 * 24 * 30);
    $rule2Array = [
        (new GcRule())->setMaxAge($rule2Duration1),
        (new GcRule())->setMaxNumVersions(2)
    ];
    $rule2Intersection->setRules($rule2Array);
    $rule2 = new GcRule();
    $rule2->setIntersection($rule2Intersection);

    $nestedRule = new GcRuleUnion();
    $nestedRule->setRules([
        $rule1,
        $rule2
    ]);
    $nestedRule = (new GcRule())->setUnion($nestedRule);

    $columnFamily5->setGCRule($nestedRule);

    $columnModification = new Modification();
    $columnModification->setId('cf5');
    $columnModification->setCreate($columnFamily5);
    $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

    print('Created column family cf5 with a Nested GC rule.' . PHP_EOL);
}
// [END bigtable_create_family_gc_nested]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
