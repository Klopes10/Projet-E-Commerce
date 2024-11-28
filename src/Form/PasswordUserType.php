<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PasswordUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actualPassword', PasswordType::class,
            [
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'placeholder' => 'Inscrivez votre mot de passe actuel'
                ],
                'mapped' => false
            ])
            ->add('plainPassword', RepeatedType::class,
            [
                'type' => PasswordType::class,
                'constraints' => [
                  new Length([
                    'min' => 4,
                    'max' => 30,
                    'minMessage' => "Mot de passe trop court, choisissez-en un plus long",
                    'maxMessage' => "Mot de passe trop long, choisissez-en un plus court"
                  ])  
                ],
                'first_options'     =>  [
                    'label' => 'Votre nouveau mot de passe',
                    'attr' => [ 
                        'placeholder' => 'Choisissez votre mot de passe'
                    ], 
                    'hash_property_path'=> 'password'
                ],
                'second_options'    =>  [
                    'label' => 'Confirmez votre nouveau mot de passe',
                    'attr' => [
                        'placeholder' => "Confirmez votre mot de passe"
                    ]
                ],
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class,
            [
                'label' => "Mettre à jour",
                'attr' =>[
                    'class' => "btn btn-success"
                ]
            ])
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {         // permet de récup le mdp saisi par l'utilisateur
                $form = $event->getForm();                                                // et le comparer avec celui en bdd si != envoyez une erreur
                $user = $form->getConfig()->getOptions()['data'];
                $passwordHasher = $form->getConfig()->getOptions()['passwordHasher'];

                //1. Recup mdp saisi par l'utilisateur
                
                $isValid = $passwordHasher->isPasswordValid($user, $form->get('actualPassword')->getData());
                
                if(!$isValid){
                    $form->get('actualPassword')->addError(new FormError("Votre mot de passe actuel est incorrecte, veuillez réessayer."));
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => User::class,
            'passwordHasher' => null
        ]);
    }
}
