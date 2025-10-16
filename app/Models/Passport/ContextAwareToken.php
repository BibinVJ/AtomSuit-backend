<?php

namespace App\Models\Passport;

use Laravel\Passport\Token;

class ContextAwareToken extends Token
{
    /**
     * Get the database connection for the model.
     *
     * @return string|null
     */
    public function getConnectionName(): ?string
    {
        // Use tenant connection if we're in a tenant context, otherwise use default (central)
        return tenant() ? 'tenant' : $this->connection;
    }

    /**
     * Create a new instance of the model for the current context
     */
    public static function forCurrentContext()
    {
        $instance = new static();
        return $instance->setConnection(tenant() ? 'tenant' : null);
    }

    /**
     * Override the find method to use context-aware connection
     */
    public static function find($id, $columns = ['*'])
    {
        return static::forCurrentContext()->where('id', $id)->first($columns);
    }

    /**
     * Create a new Eloquent query builder for the model in current context
     */
    public function newQuery()
    {
        $this->setConnection($this->getConnectionName());
        return parent::newQuery();
    }

    /**
     * Define the client relationship to use central connection
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Always get client from central database (not tenant database)
        $client = new \Laravel\Passport\Client();
        $client->setConnection(null); // Use default (central) connection
        return $this->belongsTo(get_class($client), 'client_id', 'id');
    }

    /**
     * Define the user relationship to use current context connection
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Use the appropriate user model based on context
        if (tenant()) {
            return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
        } else {
            return $this->belongsTo(\App\Models\CentralUser::class, 'user_id', 'id');
        }
    }
}