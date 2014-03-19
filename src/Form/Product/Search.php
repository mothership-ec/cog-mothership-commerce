<?php

namespace Message\Mothership\Commerce\Form\Product;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Search extends Form\AbstractType
{
    protected $_minTermLength;
    protected $_categories;

    /**
     * Constructor
     *
     * @param array  $categories    Categories for product category choice field
     * @param int    $minTermLength minimum search term length
     */
    public function __construct(array $categories, $minTermLength)
    {
        $this->setProductCategories($categories);
        $this->setMinTermLength($minTermLength);
    }

    /**
     * Sets minimum search term length
     *
     * @param  int           $minTermLength minimum search term length
     *
     * @return ProductSearch $this for chainability
     */
    public function setMinTermLength($minTermLength)
    {
        $this->_minTermLength = (int) $minTermLength;

        return $this;
    }

    /**
     * Gets minimum search term length
     *
     * @return int minimum search term length
     */
    public function getMinTermLength()
    {
        return $this->_minTermLength;
    }

    /**
     * Sets product categories for product category choice field
     *
     * @param array $categories Product categories for choice field
     *
     * @return ProductSearch $this for chainability
     */
    public function setProductCategories(array $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     * Get product categories for choice field
     * @return  [description]
     */
    public function getProductCategories()
    {
        return $this->_categories;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $categories = $options['product_categories'];
        // we want to get the category name back by the form
        $choices = [];
        foreach($categories as $category) {
            $choices[$category] = $category;
        }

        $builder->add('name', 'text', [
            'label' => 'Product Name',
            'constraints' => new Constraints\Length(['min' => self::MIN_TERM_LENGTH]),
        ]);

        $builder->add('description', 'text', [
            'label' => 'Product Description',
            'constraints' => new Constraints\Length(['min' => self::MIN_TERM_LENGTH]),
        ]);

        $builder->add('category', 'choice', [
            'label'   => 'Product Category',
            'constraints' => new Constraints\Length(['min' => self::MIN_TERM_LENGTH]),
            'choices' => $choices,
        ]);
    }

    /**
     * {@inheritdoc}
     * Adds `product_category` as required option and `min_term_length` as optional
     * option.
     * Sets defaults for these to $_categories and $_minTermLength.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['product_categories']);
        $resolver->setOptional(['min_term_length']);
        $resolver->setDefaults([
            'product_categories' => $this->_categories,
            'min_term_length'    => $this->_minTermLength,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_search';
    }

}