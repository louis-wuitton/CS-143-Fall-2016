/* Movie Constraints*/
/* Movie id should not be NULL*/
INSERT INTO Movie VALUES(NULL, 'Louis Adventure', 2014, 'R', 'Hehehe');

/* Movie id must be unique*/
INSERT INTO Movie VALUES(272, 'Louis Adventure', 2014, 'R', 'HEHEHE');

/* According to Google, the first movie was made in 1895, so any movie made before that is invalid*/
INSERT INTO Movie VALUES(0, 'Louis Adventure', 783, 'R', 'Hehehe');

/* Movie id should not exceed the maximum limit*/
INSERT INTO Movie VALUES (700000, 'Louis Adventure', 2016, 'PG-13', 'Hehehe');


/* Actor id should not be NULL */
INSERT INTO Actor VALUES (NULL, 'Wu', 'Louis', 'Male', 1995, NULL);
/* Actor id should be unique */

INSERT INTO Actor VALUES (1, 'Wu', 'Louis', 'Male', 1995, NULL);

/* Actor's date of death should not be smaller than actor's date of birth */
INSERT INTO Actor VALUES (2132, 'Wu', 'Louis', 'Male', 1995, 1992);

/* Actor's sex is neither male nor female */
INSERT INTO Actor VALUES (2012, 'Wu', 'Louis', 'Neutral', 1995, NULL);

/* Actor's id should not exceed max person id */
INSERT INTO Actor VALUES (69001, 'Wu', 'Louis', 'Male', 1995, NULL);


/* Director ID should not be NULL */
INSERT INTO Director VALUES (NULL, 'Kim', 'John', 1996, NULL);


/* Director ID cannot be duplicate */
INSERT INTO Director VALUES (37146, 'Kim', 'John', 1996, NULL)i;

/* Director's date of death is smaller than director's date of birth */

INSERT INTO Director VALUES (37146, 'Kim', 'John'. 1996, 1991);


/* MovieGenre mid has to be in the movie table */
INSERT INTO MovieGenre VALUES (15000, 'Drama');

/* MovieDirector mid has to exist in the movie table*/
INSERT INTO MovieDirector VALUES (15000, 232);

/* MovieDirector did has to exist in the director table */
INSERT INTO MovieDirector VALUES (23, 15000);

/* MovieActor must has mid that exists in Movie table */
INSERT INTO MovieActor VALUES (15000, 1, 'TA For CS 143');


/* MovieActor aid must exists in Actor */
INSERT INTO MovieActor VALUES (3, 2, 'Professor Cho');

/* The movie ID in the review must exists in Movie table */
INSERT INTO Review VALUES ('John Cho', '10-16-2016 00:00:03', 15000, 4, 'So bad, I will fail you in CS143'); 

/* The rating in review should lie within 0 and 5 */
INSERT INTO Review VALUES ('John Cho', '10-16-2016 00:00:03', 2, 7, 'So good that I want to rate it greater than 5');




