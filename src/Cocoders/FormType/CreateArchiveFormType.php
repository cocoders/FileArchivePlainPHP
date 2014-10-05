<?php

namespace Cocoders\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Regex;

class CreateArchiveFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('archiveName', 'text', ['required' => true])
            ->add('path', 'text')
            ->add('nameNumberSuffixIsRequired', 'checkbox', ['required' => false])
        ;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if (isset($data['nameNumberSuffixIsRequired']) && $data['nameNumberSuffixIsRequired']) {
                $form->remove('archiveName');
                $form->add(
                    'archiveName',
                    'text',
                    [
                        'required' => true,
                        'constraints' => new Regex([
                            'pattern' => '/.*[0-9]+/'
                        ])
                    ]
                );
            }
        });
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'archive_type';
    }
}