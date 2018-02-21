<?php

use Hal\Core\Database\PhinxMigration;
if (!class_exists(PhinxMigration::class)) { require_once __DIR__ . '/../src/Database/PhinxMigration.php'; }

class InitialSchema extends PhinxMigration
{
    public function change()
    {
        $this->createUserTables();
        $this->createApplicationTables();
        $this->createTargetTables();
        $this->createJobTables();

        // environment
        $this->createUUIDTable('environments')
            ->addColumn('created',       'datetime',   [])
            ->addColumn('name',          'string',     ['limit' => 20])
            ->addColumn('is_production', 'boolean',    ['default' => false])
            ->update();

        // system settings
        $this->createUUIDTable('system_settings')
            ->addColumn('created',   'datetime', [])
            ->addColumn('name',      'string',   ['limit' => 100])
            ->addColumn('value',     'text',     $this->textOptions('64kb'))
            ->update();

        // audit events
        $this->createUUIDTable('audit_events')
            ->addColumn('created',      'datetime',  [])
            ->addColumn('action',       'string',    $this->enumOptions('create'))
            ->addColumn('actor',        'string',    ['limit' => 100])
            ->addColumn('description',  'string',    ['limit' => 200])
            ->addColumn('parameters',   'json',      [])
            ->update();

        // credentials
        $this->createUUIDTable('credentials')
            ->addColumn('created',             'datetime',  [])
            ->addColumn('credential_type',     'string',    $this->enumOptions('aws_static'))
            ->addColumn('name',                'string',    ['limit' => 100])
            ->addColumn('is_internal',         'boolean',   ['default' => false])

            ->addColumn('awsstatic_key',       'string',    ['limit' => 100])
            ->addColumn('awsstatic_secret',    'text',      $this->textOptions('64kb'))
            ->addColumn('awsrole_account',     'string',    ['limit' => 25])
            ->addColumn('awsrole_role',        'string',    ['limit' => 100])

            ->addColumn('privatekey_username', 'string',    ['limit' => 100])
            ->addColumn('privatekey_path',     'string',    ['limit' => 200])
            ->addColumn('privatekey_file',     'text',      $this->textOptions('64kb'))

            ->addColumn('application_id',      'uuid',      ['null' => true])
            ->addColumn('organization_id',     'uuid',      ['null' => true])
            ->addColumn('environment_id',      'uuid',      ['null' => true])

            ->update();

        // encrypted properties
        $this->createUUIDTable('encrypted_properties')
            ->addColumn('created',             'datetime',  [])
            ->addColumn('name',                'string',    ['limit' => 100])
            ->addColumn('secret',              'text',      $this->textOptions('64kb'))

            ->addColumn('application_id',      'uuid',      ['null' => true])
            ->addColumn('organization_id',     'uuid',      ['null' => true])
            ->addColumn('environment_id',      'uuid',      ['null' => true])

            ->update();

        // scheduled actions / jobs
        $this->createUUIDTable('scheduled_actions')
            ->addColumn('created',             'datetime',  [])
            ->addColumn('status',              'string',    $this->enumOptions('pending'))

            ->addColumn('message',             'string',    ['limit' => 200])
            ->addColumn('parameters',          'json',      [])

            ->addColumn('trigger_job_id',      'uuid',      ['null' => true])
            ->addColumn('scheduled_job_id',    'uuid',      ['null' => true])
            ->addColumn('user_id',             'uuid',      ['null' => true])

            ->update();
    }

    public function createUserTables()
    {
        // user providers
        $this->createUUIDTable('system_identity_providers')
            ->addColumn('created',       'datetime', [])
            ->addColumn('name',          'string',   ['limit' => 100])
            ->addColumn('is_oauth',      'boolean',  ['default' => false])
            ->addColumn('provider_type', 'string',   $this->enumOptions('internal'))
            ->addColumn('parameters',    'json',     [])
            ->update();

        // users
        $this->createUUIDTable('users')
            ->addColumn('created',      'datetime', [])
            ->addColumn('name',         'string',   ['limit' => 100])
            ->addColumn('settings',     'json',     [])
            ->addColumn('is_disabled',  'boolean',  ['default' => false])
            ->update();

        // users - identities
        $this->createUUIDTable('users_identities')
            ->addColumn('created',            'datetime', [])
            ->addColumn('provider_unique_id', 'string',   ['limit' => 100])
            ->addColumn('parameters',         'json',     [])

            ->addColumn('user_id',            'uuid',     [])
            ->addColumn('provider_id',        'uuid',     [])
            ->update();

        // users - tokens
        $this->createUUIDTable('users_tokens')
            ->addColumn('created',   'datetime',  [])
            ->addColumn('name',      'string',    ['limit' => 100])
            ->addColumn('value',     'string',    ['limit' => 100])
            ->addColumn('user_id',   'uuid',      [])
            ->update();

        // users - permissions
        $this->createUUIDTable('users_permissions')
            ->addColumn('created',         'datetime',  [])
            ->addColumn('permission_type', 'string',    $this->enumOptions('member'))
            ->addColumn('user_id',         'uuid',      [])

            ->addColumn('application_id',  'uuid',      ['null' => true])
            ->addColumn('organization_id', 'uuid',      ['null' => true])
            ->addColumn('environment_id',  'uuid',      ['null' => true])
            ->update();
    }

    public function createApplicationTables()
    {
        // organization
        $this->createUUIDTable('organizations')
            ->addColumn('created',    'datetime', [])
            ->addColumn('name',       'string',   ['limit' => 100])
            ->update();

        // vcs settings
        $this->createUUIDTable('system_vcs_providers')
            ->addColumn('created',    'datetime', [])
            ->addColumn('name',       'string',   ['limit' => 100])
            ->addColumn('vcs_type',   'string',   $this->enumOptions('ghe'))
            ->addColumn('parameters', 'json',     [])
            ->update();

        // applications
        $this->createUUIDTable('applications')
            ->addColumn('created',           'datetime', [])
            ->addColumn('name',              'string',   ['limit' => 100])
            ->addColumn('parameters',        'json',     [])
            ->addColumn('is_disabled',       'boolean',  ['default' => false])
            ->addColumn('provider_id',       'uuid',     ['null' => true])
            ->addColumn('organization_id',   'uuid',     ['null' => true])
            ->update();
    }

    public function createTargetTables()
    {
        // groups
        $this->createUUIDTable('targets_templates')
            ->addColumn('created',         'datetime',  [])
            ->addColumn('target_type',     'string',    $this->enumOptions('script'))
            ->addColumn('name',            'string',    ['limit' => 100])
            ->addColumn('parameters',      'json',      [])

            ->addColumn('application_id',  'uuid',      ['null' => true])
            ->addColumn('organization_id', 'uuid',      ['null' => true])
            ->addColumn('environment_id',  'uuid',      ['null' => true])
            ->update();

        // targets
        $this->createUUIDTable('targets')
            ->addColumn('created',        'datetime', [])
            ->addColumn('target_type',    'string',   $this->enumOptions('script'))
            ->addColumn('name',           'string',   ['limit' => 100])
            ->addColumn('url',            'string',   ['limit' => 200])
            ->addColumn('parameters',     'json',     [])

            ->addColumn('application_id', 'uuid',     ['null' => false])
            ->addColumn('environment_id', 'uuid',     ['null' => true])

            ->addColumn('template_id',    'uuid',     ['null' => true])
            ->addColumn('credential_id',  'uuid',     ['null' => true])
            ->addColumn('last_job_id',    'uuid',     ['null' => true])
            ->update();
    }

    public function createJobTables()
    {
        // jobs
        $this->createUUIDTable('jobs')
            ->addColumn('created',        'datetime',   [])
            ->addColumn('job_type',       'string',     $this->enumOptions('build'))
            ->addColumn('job_status',     'string',     $this->enumOptions('pending'))
            ->addColumn('parameters',     'json',       [])

            ->addColumn('start',          'datetime',   ['null' => true])
            ->addColumn('job_end',        'datetime',   ['null' => true])

            ->addColumn('user_id',        'uuid',       ['null' => false])
            ->update();

        // jobs - builds
        $this->createUUIDTable('jobs_builds')
            ->addColumn('code_reference',   'string',     ['limit' => 100])
            ->addColumn('code_commit_sha',  'string',     ['limit' => 100])

            ->addColumn('application_id',   'uuid',       ['null' => true])
            ->addColumn('environment_id',   'uuid',       ['null' => true])
            ->update();

        // jobs - releases
        $this->createUUIDTable('jobs_releases')
            ->addColumn('build_id',       'uuid',       ['null' => false])

            ->addColumn('application_id', 'uuid',       ['null' => true])
            ->addColumn('environment_id', 'uuid',       ['null' => true])
            ->addColumn('target_id',      'uuid',       ['null' => true])
            ->update();

        // jobs - events
        $this->createUUIDTable('jobs_events')
            ->addColumn('created',        'datetime',   [])
            ->addColumn('stage',          'string',     $this->enumOptions('unknown'))
            ->addColumn('status',         'string',     $this->enumOptions('info'))
            ->addColumn('event_duration', 'integer',    [])
            ->addColumn('event_order',    'integer',    [])
            ->addColumn('message',        'string',     ['limit' => 200])
            ->addColumn('parameters',     'binary',     $this->blobOptions('16mb'))

            ->addColumn('job_id',         'uuid',       ['null' => false])
            ->update();

        // jobs - meta
        $this->createUUIDTable('jobs_meta')
            ->addColumn('created',     'datetime',     [])
            ->addColumn('name',        'string',       ['limit' => 100])
            ->addColumn('value',       'text',         $this->textOptions('64kb'))

            ->addColumn('job_id',         'uuid',       ['null' => false])
            ->update();

        // jobs - artifacts
        $this->createUUIDTable('jobs_artifacts')
            ->addColumn('created',        'datetime',   [])
            ->addColumn('name',           'string',     ['limit' => 100])
            ->addColumn('is_removable',   'boolean',    ['default' => true])

            ->addColumn('parameters',     'json',       [])

            ->addColumn('job_id',         'uuid',       ['null' => false])
            ->update();
    }
}
