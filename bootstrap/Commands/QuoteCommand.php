<?php

namespace Bootstrap\Commands;

class QuoteCommand
{
    public string $name = 'quote';
    public string $description = 'Display a random inspirational quote';

    protected array $quotes = [
        "“Simplicity is the soul of efficiency.” – Austin Freeman",
        "“Talk is cheap. Show me the code.” – Linus Torvalds",
        "“Code is like humor. When you have to explain it, it’s bad.” – Cory House",
        "“The best error message is the one that never shows up.” – Thomas Fuchs",
        "“First, solve the problem. Then, write the code.” – John Johnson",
        "“The ethereal way to build Asterisk applications.” – Astereal",
        "“Do not go where the path may lead, go instead where there is no path and leave a trail.” – Ralph Waldo Emerson",
        
    ];

    public function handle(array $args)
    {
        $quote = $this->quotes[array_rand($this->quotes)];
        $this->display($quote);
    }

    protected function display(string $quote)
    {
        $line = str_repeat('-', strlen($quote) + 4);
        echo "\n$line\n";
        echo "| $quote |\n";
        echo "$line\n\n";
    }
}
