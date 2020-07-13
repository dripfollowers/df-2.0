SELECT distinct(ID), post_date, 
customer.meta_value as customer,
contact.meta_value as contact,
service.meta_value as service,
pack.meta_value as pack,
total_number.meta_value as total_number,
target.meta_value as target,
upsell.meta_value as upsell,
payment_status.meta_value as payment_status,
payment_amount.meta_value as payment_amount,


FROM wp_posts as orders,
wp_postmeta as customer,
wp_postmeta as contact,
wp_postmeta as service,
wp_postmeta as pack,
wp_postmeta as total_number,
wp_postmeta as target,
wp_postmeta as upsell,
wp_postmeta as payment_status,
wp_postmeta as payment_amount

WHERE orders.ID = customer.post_id
AND orders.ID = contact.post_id
AND orders.ID = service.post_id
AND orders.ID = pack.post_id
AND orders.ID = total_number.post_id
AND orders.ID = target.post_id
AND orders.ID = upsell.post_id
AND orders.ID = payment_status.post_id
AND orders.ID = payment_amount.post_id


AND orders.post_type = 'driporder'
AND customer.meta_key = 'order-customer'
AND contact.meta_key = 'order-contact-email'
AND service.meta_key = 'order-service'
AND pack.meta_key = 'order-pack'
AND total_number.meta_key = 'order-number'
AND target.meta_key = 'order-target'
AND upsell.meta_key = 'order-with-upsell'
AND payment_status.meta_key = 'order-payment-status'
AND payment_amount.meta_key = 'order-payment-amount'



SELECT ID, post_date, 
customer.meta_value as customer,
contact.meta_value as contact,
service.meta_value as service,
pack.meta_value as pack,
total_number.meta_value as total_number,
target.meta_value as target,
upsell.meta_value as upsell,
payment_status.meta_value as payment_status,
payment_amount.meta_value as payment_amount,
trx_id.meta_value as trx_id,
progress.meta_value as progress,
remarks.meta_value as remarks

FROM wp_posts as orders,
wp_postmeta as customer,
wp_postmeta as contact,
wp_postmeta as service,
wp_postmeta as pack,
wp_postmeta as total_number,
wp_postmeta as target,
wp_postmeta as upsell,
wp_postmeta as payment_status,
wp_postmeta as payment_amount,
wp_postmeta as trx_id,
wp_postmeta as progress,
wp_postmeta as remarks

WHERE orders.ID = customer.post_id
AND orders.ID = contact.post_id
AND orders.ID = service.post_id
AND orders.ID = pack.post_id
AND orders.ID = total_number.post_id
AND orders.ID = target.post_id
AND orders.ID = upsell.post_id
AND orders.ID = payment_status.post_id
AND orders.ID = payment_amount.post_id
AND orders.ID = trx_id.post_id
AND orders.ID = progress.post_id
AND orders.ID = remarks.post_id

AND orders.post_type = 'driporder'
AND customer.meta_key = 'order-customer'
AND contact.meta_key = 'order-contact-email'
AND service.meta_key = 'order-service'
AND pack.meta_key = 'order-pack'
AND total_number.meta_key = 'order-number'
AND target.meta_key = 'order-target'
AND upsell.meta_key = 'order-with-upsell'
AND payment_status.meta_key = 'order-payment-status'
AND payment_amount.meta_key = 'order-payment-amount'
AND trx_id.meta_key = 'order-trx-id'
AND progress.meta_key = 'order-progress'
AND remarks.meta_key = 'order-remarks'

