<?php

namespace App\Commands;

use App\Exceptions\LogicException;
use App\Exceptions\UnauthorizedException;

class LoginCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'login';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Authenticate with Laravel Forge';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $token = $this->ask('API Token');

        $this->config->set('token', $token);

        $email = $this->ensureApiTokenIsValid($token);

        $this->ensureCurrentTeamIsSet();

        $this->info("Authenticated successfully as [$email].");
    }

    /**
     * Ensure the given api token is valid.
     *
     * @param  string  $token
     * @return string
     */
    protected function ensureApiTokenIsValid($token)
    {
        try {
            return $this->forge->user()->email;
        } catch (UnauthorizedException $e) {
            $this->config->flush();

            throw $e;
        }
    }

    /**
     * Ensure the current team is set in the configuration file.
     *
     * @return void
     */
    protected function ensureCurrentTeamIsSet()
    {
        $server = collect($this->forge->servers())->first();

        if ($server == null) {
            throw new LogicException(
                'Please create a server first.'
            );
        }

        $this->config->set('server', $server->id);
    }
}