<?php

class ZendR_Store_Cart_Item
{
    private $_product = null;

	private $_quantity = 0;

    private $_subTotal = null;

    public function __construct(ZendR_Store_Product $product = null, $qty = null)
    {
        $this->_product = $product;
        $this->_quantity = (int)$qty;
        $this->calculateImporte();
    }

	/**
	 *
	 * @return ZendR_Store_Product
	 */
    public function getProduct()
    {
        return $this->_product;
    }

	public function setProduct(ZendR_Store_Product $product)
    {
        $this->_product = $product;
        $this->calculateImporte();
    }

    public function getId()
    {
        return $this->_product->getId();
    }

    public function setId($value)
    {
        $this->_product->setId((int)$value);
    }

    public function getName()
    {
        return $this->_product->getName();
    }

    public function setName($value)
    {
        $this->_product->setName($value);
    }

    public function getPrice()
    {
        return $this->_product->getPrice();
    }

    public function setPrice($value)
    {
        $this->_product->setPrice((double)$value);
    }

    public function getQuantity()
    {
        return $this->_quantity;
    }

    public function setQuantity($value)
    {
        $this->_quantity = (int)$value;
        $this->calculateImporte();
    }

    public function getWeight()
    {
        return $this->_product->getWeight();
    }

    public function setWeight($value)
    {
        $this->_product->setWeight((double)$value);
    }

    public function calculateImporte()
    {
        $this->getSubTotal();
    }

    public function getImporte()
    {
        return $this->_subTotal;
    }

    public function getSubTotal()
    {
        if ($this->getPrice() != 0 && null !== $this->getPrice()) {
            $this->setSubTotal($this->getQuantity() * $this->getPrice());
            return $this->_subTotal;
        }
        $this->setSubTotal(0);
        return $this->_subTotal;
    }

    public function setSubTotal($value)
    {
        $this->_subTotal = (double)$value;
    }
}
