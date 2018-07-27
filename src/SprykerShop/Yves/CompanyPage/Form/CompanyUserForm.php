<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Form;

use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyUserForm extends AbstractType
{
    public const FIELD_ID_CUSTOMER = 'id_customer';
    public const FIELD_ID_COMPANY_USER = 'id_company_user';
    public const FIELD_SALUTATION = 'salutation';
    public const FIELD_FIRST_NAME = 'first_name';
    public const FIELD_LAST_NAME = 'last_name';
    public const FIELD_EMAIL = 'email';
    public const FIELD_IS_GUEST = 'is_guest';
    public const FIELD_FK_COMPANY = 'fk_company';
    public const FIELD_FK_CUSTOMER = 'fk_customer';
    public const FIELD_FK_COMPANY_BUSINESS_UNIT = 'fk_company_business_unit';
    public const FIELD_COMPANY_ROLE_COLLECTION = 'company_role_collection';

    public const OPTION_BUSINESS_UNIT_CHOICES = 'business_unit_choices';
    public const OPTION_COMPANY_ROLE_CHOICES = 'company_role_choices';

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(static::OPTION_BUSINESS_UNIT_CHOICES);
        $resolver->setRequired(static::OPTION_COMPANY_ROLE_CHOICES);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'companyUserForm';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addIdCompanyUserField($builder)
            ->addIdCustomerField($builder)
            ->addFkCompanyField($builder)
            ->addFkCustomerField($builder)
            ->addFkCompanyBusinessUnitField($builder, $options)
            ->addCompanyRoleCollectionField($builder, $options)
            ->addSalutationField($builder)
            ->addFirstNameField($builder)
            ->addLastNameField($builder)
            ->addEmailField($builder)
            ->addIsGuestField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addFkCompanyField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_FK_COMPANY, HiddenType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIdCompanyUserField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_ID_COMPANY_USER, HiddenType::class, [
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIdCustomerField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_ID_CUSTOMER, HiddenType::class, [
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addFkCustomerField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_FK_CUSTOMER, HiddenType::class, [
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return $this
     */
    protected function addFkCompanyBusinessUnitField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(static::FIELD_FK_COMPANY_BUSINESS_UNIT, ChoiceType::class, [
            'label' => 'company.account.company_user.business_unit',
            'required' => true,
            'choices' => array_flip($options[static::OPTION_BUSINESS_UNIT_CHOICES]),
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addSalutationField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_SALUTATION, ChoiceType::class, [
            'choices' => array_flip([
                'Mr' => 'company.user.salutation.mr',
                'Ms' => 'company.user.salutation.ms',
                'Mrs' => 'company.user.salutation.mrs',
                'Dr' => 'company.user.salutation.dr',
            ]),
            'required' => true,
            'label' => 'address.salutation',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addFirstNameField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_FIRST_NAME, TextType::class, [
            'label' => 'company.user.first_name',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addLastNameField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_LAST_NAME, TextType::class, [
            'label' => 'company.user.last_name',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addEmailField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_EMAIL, EmailType::class, [
            'label' => 'auth.email',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIsGuestField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_IS_GUEST, HiddenType::class, [
            'data' => false,
        ]);

        $this->addIsGuestTransformer($builder);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return void
     */
    protected function addIsGuestTransformer(FormBuilderInterface $builder): void
    {
        $builder->get(static::FIELD_IS_GUEST)->addModelTransformer(new CallbackTransformer(
            function ($isGuest) {
                return $isGuest;
            },
            function ($isGuestSubmittedValue) {
                return (bool)$isGuestSubmittedValue;
            }
        ));
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return $this
     */
    protected function addCompanyRoleCollectionField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(static::FIELD_COMPANY_ROLE_COLLECTION, ChoiceType::class, [
            'choices' => array_flip($options[static::OPTION_COMPANY_ROLE_CHOICES]),
            'required' => true,
            'label' => 'company.account.company_role',
            'multiple' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $this->addRoleCollectionTransformer($builder);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return void
     */
    protected function addRoleCollectionTransformer(FormBuilderInterface $builder): void
    {
        $builder->get(static::FIELD_COMPANY_ROLE_COLLECTION)->addModelTransformer(new CallbackTransformer(
            function ($roleCollection) {
                return $roleCollection;
            },
            function ($roleCollectionSubmitted) {
                $companyRoleCollectionTransfer = new CompanyRoleCollectionTransfer();

                foreach ($roleCollectionSubmitted as $role) {
                    $companyRoleTransfer = new CompanyRoleTransfer();
                    $companyRoleTransfer->setIdCompanyRole($role);

                    $companyRoleCollectionTransfer->addRole($companyRoleTransfer);
                }

                return $companyRoleCollectionTransfer;
            }
        ));
    }
}
