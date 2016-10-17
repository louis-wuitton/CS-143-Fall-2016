SELECT CONCAT(first, ' ', last)
FROM Actor 
WHERE id IN (
	SELECT aid 
	FROM Movie, MovieActor
	WHERE id = mid AND
	title = "Die Another Day"
);



SELECT COUNT(*)
FROM
( SELECT DISTINCT Actor.id
  FROM MovieActor, Actor
  WHERE Actor.id = MovieActor.aid
  GROUP BY Actor.id
  HAVING COUNT(MovieActor.mid) > 1
) A;


/*
    Return the names of directors who have produced movies released after 2002
*/

SELECT CONCAT(first, ' ', last)
FROM Director
WHERE id IN (
	SELECT DISTINCT did 
	FROM Movie, MovieDirector
	WHERE id = mid AND
	year > 2002
);
