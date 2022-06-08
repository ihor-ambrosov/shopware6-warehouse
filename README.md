# Multi-Warehouse for Shopware 6
###### Shopware: 6.4

Inventory management is a crucial part of electronic commerce. It is twice important when you have two or more warehouses/suppliers/drop shippers located worldwide / nationwide. That requires proper inventory management software.
Multi-Warehouse extension is a powerful tool in a warehouse management system that supports multi-warehouse functionality. It allows to set up multiple shipping origins within one installation, control stock in different physical locations through the same admin interface as well as many other functions for a shop.

## Features
 - Manage warehouses
 - Split stock by warehouses for each product
 - Import/export stocks per warehouse
 - Allow customers to choose a warehouse per sales channel

## Warehouses
To manage warehouses, tap **Catalogues → Warehouses** in the administrator menu.

![admin-warehouse-index](https://user-images.githubusercontent.com/107030606/172604932-c53c283b-d6a5-464b-9719-ace7ad6ee9b7.png)

The system creates a **Default** warehouse automatically. It can't be removed.
To create a warehouse, click **Add warehouse**. Or click the **Edit** action link to edit a warehouse that was already created.

![admin-warehouse-edit](https://user-images.githubusercontent.com/107030606/172604926-eaef158f-eba1-4a4e-8965-574b97e9281a.png)

The list of warehouse fields:

 - **Code**: a unique warehouse code.
 - **Name**: a warehouse name. It can be set for each language.
 - **Priority**: a warehouse sorting priority. Warehouses with the highest priorities are listed first.

## Products
Select **Catalogues → Products** in the administrator menu.

![admin-product-index](https://user-images.githubusercontent.com/107030606/172604915-969dae4b-ed09-4674-a8c8-29d3306472b1.png)

You can see that the module adjusts **In stock**, **Available** columns to display information for each warehouse.
Next, click **Add product** button to create a new product. Or click **Edit** action link to edit any of the previously created products..

![admin-product-edit-1](https://user-images.githubusercontent.com/107030606/172604904-a9edee12-aa30-46e3-993d-1ff043146aca.png)

Select **Warehouses** for a product. Once done, you can enter the **Stock** field value for each warehouse.

![admin-product-edit-2](https://user-images.githubusercontent.com/107030606/172604912-c02dca1a-d551-439b-9f92-b92f5081c14f.png)

## Import/Export Products
Select **Settings → Shop → Import/Export → Profiles** in the administrator menu.
You can create new or edit existing product profiles here.

![admin-importexport-profile-edit](https://user-images.githubusercontent.com/107030606/172604870-20febc9e-48b1-4ac9-b864-d71d3f16a454.png)

The module extends standard mappings to import/export warehouse-specific fields for products. A database entry path starts with **productWarehouses**. Next, a warehouse code follows. The third part is a warehouse-specific field itself (e.g. stock).

## Orders
Tap **Orders → Overview** in the administrator menu.

![admin-order-index](https://user-images.githubusercontent.com/107030606/172604887-0d9556b0-7288-4e53-885f-86b6063938a4.png)

Each order has a warehouse assigned (see **Warehouse** column).
Create a new order by clicking **Add order** button.

![admin-order-create](https://user-images.githubusercontent.com/107030606/172604875-92f7fb4a-f27e-4190-98cb-d07fc10adb80.png)

A warehouse must be selected in addition to the standard create order process. The **Warehouse** field is placed next to **Sales Channel**. Once, a warehouse is selected you can choose items from that warehouse.

## Customer Area
On the module installation, a customer can check products availability per warehouse and buy products from different warehouses.
A warehouse switcher is placed next to the currency selector.

![product-index-1](https://user-images.githubusercontent.com/107030606/172604952-7867da0d-1724-46db-b966-ddaa7650b865.png)

Each order has assigned warehouse information reflected.

![order-index](https://user-images.githubusercontent.com/107030606/172604938-cd60ada0-84b0-4fd0-a1c1-83d8168fc5d4.png)



