Tianyan Wu
twu1995629@g.ucla.edu

A list of optimization I provided:
1. If there is any invalid query, my code would catch it directly without going over the B+ Tree or reading the record file.
For example:
SELECT * FROM small WHERE key = 100 AND key = 1000
Another tricky invalid case, when overflow happens:
SELECT * FROM small WHERE key < -1298311236178461

2. When we count the total number of tuples, we would like to skip the reading from record files because that’s not very necessary. So I use the B+ tree to find the leftmost element on leaf level and count all the keys without doing any read. Using B+ tree could guarantee that we count the correct number of tuples in the table.

3. For a normal select query, if we specify a range, then the B+ tree will first locate to the lower bound of the range then start looking over the tuples.
For example: SELECT * FROM large WHERE key > 100

4. If you have a case like this:
SELECT * FROM large WHERE key = 3619 AND value = ‘Seconds’, and the key and value match to the same tuple, then what my implementation will do is first look at the tuple stored with the key 3619 and see if the value actually matches. This will save a lot of page reading. 


Information that is useful: 
The first part of the project is to finish the load function. The load function is written by using the APIs for Bruinbase. I used the file access APIs in combination with fstream to write the function. All the operations includes opening the movie.del file, reading it by line and append it into the movie.tbl file. If anything goes wrong then return the error code. 

For part B the way I designed a leaf node is by reserving the last 4 bytes on buffer for next page ID pointer, and reserving the second last 4 bytes for the key count. This design could handle the case when key equals to 0 properly.

After starting part C, I decided to divorce with my previous partner, John Kim. So from now on I am doing for my own. Thus the grade of this project should not be shared with John anymore. 

From Part B I couldn’t figure out a proper way to test my code, but after part C I decided to write testing script. The testing script will read the input file, such as movie.del, or xlarge.del, and it will parse it, and insert the page IDs into the page file by calling the BTreeIndex functions. There are several functions I had to take care of for 2C: insert, locate, readForward, open and close. These functions are essential for the overall behavior of Bruincase. I used recursion to traverse throughout the B+ Tree. I keep track of where I am by pass in a variable called height, and if my current height is same as the height of B+ tree then I know that I am at the leafNode and I should insert into the leaf. However, since the recursion will return back to the previous calling instance, so that makes it easier to handle overflow immediately after it happens. So if leaf node overflow happens, then very easily we split current leaf, and put the first key of newly created leaf node to the level right above leaf node. If non-leaf node happens then do split, but move up the key in the middle. The algorithm is basically what Professor Cho covered during the lecture. 

Several things I want to say is that when I did project 2C I started to realize a lot of things I didn’t realize when I was doing project 2B. For example, each tree node has a getKeyCount function. In 2B I decided to just use a private variable called key_count and for getKeyCount function I just returns it. But after 2C I realized that BTreeNode is simply just like a reader, or a temporary cache for a page, thus whenever we create a BTreeNode, the key_count will get reset to 0, thus leaving getKeyCount function completely useless. Thus from 2C I changed my implementations for getKeyCount in 2B. I understand that this may lead to point deduction, but I still want to provide a reason why I made a major changes. Another difference is the print functions, which are mainly used for debugging. Meanwhile after 2C I also changed the locateChildPtr function because it actually didn’t do properly. But overall, since I didn’t realize that I could just easily write a testing script to test my code, I had to make many changes while doing 2C. If there is a point deduction, I totally understand that.


Now for part D of the project, we need to implement the SqlEngine. Here I changed my implementation for leaf node and non leaf node again. Because I realized that I have to handle the case when key is 0, but my previously implementation did not do that properly so there are some code changes for my implementation in part B. Because of that I added another function called setKeyCount(). 

One thing I want to mention is that when you do a query like SELECT * FROM large, if we use index file it will read more pages than when you don’t use index file. But technically speaking the result you get when you don’t read index file is wrong, because the displayed tuples are not sorted. We need to make sure that the tuples are sorted. Thus we should use B+ Tree. Meanwhile, no matter what conditions we set, if we specify that we want to select either key or *, we have to use B+ tree, because we want to ensure that the output follows the order sorted by key.

Also my implementation assumes that key could be negative or zero. But my implementation does not assume that there is no duplicate for values.

Furthermore, from piazza post, I saw the TA says that “For root node, the restriction is different. You can just put one key in it.”. I just need to clarify that for all the root nodes the number of keys has different restrictions than that of the other non-leaf nodes.
