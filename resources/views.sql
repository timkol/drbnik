create view v_gossip_status as
select `g`.`gossip_id` AS `gossip_id`,`g`.`gossip` AS `gossip`,`g`.`inserted` AS `inserted`,`gh`.`gossip_history_id` AS `gossip_history_id`,`gh`.`status_id` AS `status_id`,`gh`.`login_id` AS `login_id`,`gh`.`modified` AS `modified` 
from (`gossip_history` `gh` left join `gossip` `g` on((`gh`.`gossip_id` = `g`.`gossip_id`))) 
where (`gh`.`modified` = (select max(`t2`.`modified`) from `gossip_history` `t2` where (`t2`.`gossip_id` = `g`.`gossip_id`)));

create view v_team_points as
select `t`.`name` AS `team`,coalesce(sum(`tp`.`points_change`),0) AS `points` 
from (`team` `t` left join `team_points` `tp` on((`t`.`team_id` = `tp`.`team_id`))) 
where (`tp`.`active` = 1) 
group by `t`.`team_id` 
order by coalesce(sum(`tp`.`points_change`),0) desc;