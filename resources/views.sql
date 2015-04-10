create view v_gossip_status as
select `g`.`gossip_id` AS `gossip_id`,`g`.`gossip` AS `gossip`,`g`.`inserted` AS `inserted`,`gh`.`gossip_history_id` AS `gossip_history_id`,`gh`.`status_id` AS `status_id`,`gh`.`login_id` AS `login_id`,`gh`.`modified` AS `modified` 
from (`gossip_history` `gh` left join `gossip` `g` on((`gh`.`gossip_id` = `g`.`gossip_id`))) 
where (`gh`.`modified` = (select max(`t2`.`modified`) from `gossip_history` `t2` where (`t2`.`gossip_id` = `g`.`gossip_id`))) 
