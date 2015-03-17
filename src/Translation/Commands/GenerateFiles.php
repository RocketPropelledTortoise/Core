<?php

namespace Rocket\Translation\Commands;

use Illuminate\Console\Command;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class GenerateFiles extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rocket:generate_languages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate language files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        I18N::generate();

        $this->info('Generated files');
    }
}
