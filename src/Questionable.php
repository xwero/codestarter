<?php

namespace Xwero\Codestarter;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

trait Questionable
{
    protected function getChoiceQuestion(HelperInterface $helper, InputInterface $input, OutputInterface $output, string $question, array $choices, string $errorMessage, array $multiSelectDefaults = []): array|string|false
    {
        $isMultiselect = count($multiSelectDefaults) > 0;

        $choiceDefaults = $isMultiselect ? $multiSelectDefaults : [0,1] ;
        $choiceQuestion = new ChoiceQuestion($question."\n", $choices, join(',', $choiceDefaults));
        // The multiselect setting is a hack to override the standard behavior of the choice question
        $choiceQuestion->setMultiselect(true);
        $answer = $helper->ask($input, $output, $choiceQuestion);

        if($isMultiselect){
            return $answer;
        }

        if(count($answer) > 1){
            $output->writeln($errorMessage);
            return false;
        }

        return $answer[0];
    }

    protected function getAutoCompleteQuestion(HelperInterface $helper, InputInterface $input, OutputInterface $output, string $question, array $values) : string
    {
        $question = new Question($question." (Type to complete)\n", '');
        $question->setAutocompleterValues($values);

        return $helper->ask($input, $output, $question);
    }
}