/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include "BTreeIndex.h"
#include "BTreeNode.h"
#include <cstring>

using namespace std;

/*
 * BTreeIndex constructor
 */

 //Remember, when the index is initialized, these information will be gone
BTreeIndex::BTreeIndex()
{
    treeHeight = 0;
    rootPid = -1;
    memset(buffer, '\0', sizeof(buffer));
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */

RC BTreeIndex::open(const string& indexname, char mode)
{
    RC error = pf.open(indexname, mode);
    if (error != 0){
      return error;
    }
    if (pf.endPid() == 0){
      // So you just created the root node
      treeHeight = 0;
      rootPid = -1;
      return pf.write(0, buffer);
    }

    error = pf.read(0, buffer);
    if (error != 0)
      return error;

    int saved_pid;
    int saved_height;

    memcpy(&saved_pid, buffer, sizeof(PageId));
    memcpy(&saved_height, buffer + sizeof(PageId), sizeof(int));

    //We need to have a valid page ID and tree height
    if(saved_pid > 0 && saved_height >= 0){
      rootPid = saved_pid;
      treeHeight = saved_height;
    }

    return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
    //Before closing the BTreeIndex we want to write the metadata into the buffer
    memcpy(buffer, &rootPid, sizeof(PageId));
    memcpy(buffer, &treeHeight, sizeof(int));

    RC error = pf.write(0, buffer);
    if (error != 0){
      return error;
    }
    return pf.close();
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
    //Finished the trivial case for insert
    if(key < 0){
      return RC_INVALID_ATTRIBUTE;
    }

    RC error;
    //Basic case: when we need to create a new tree
    if (treeHeight == 0){
      BTLeafNode rootNode;
      rootNode.insert(key, rid);
      if(pf.endPid() == 0){
        rootPid = 1;
      }else{
        rootPid = pf.endPid();
      }
      treeHeight++;
      return rootNode.write(rootPid, pf);
    }
    else{
        int tempKey = -1;
        int tempPid = -1;
        return insertRecur(key, rid, 1, rootPid, tempKey, tempPid);
    }
}

RC BTreeIndex::insertRecur(int key, const RecordId& rid, int height, int currentPid, int& specialKey,int& specialPid){
    RC error;
    if (height == treeHeight){
        //Now we need to insert at the leaf node
        BTLeafNode leafNode;
        //read the leafNode
        error = leafNode.read(currentPid, pf);

        if (error != 0){
          fprintf(stdout, "Error happened at line 130 %d\n", error);
          return error;
        }
        error = leafNode.insert(key, rid);
        if (error == 0){
          //fprintf(stdout, "\nPrinting out all the keys in current node ");
          //leafNode.print();
          leafNode.write(currentPid, pf);
          return 0;
        }else if (error == RC_NODE_FULL){
          //We have overflow, thus we need to create a sibling
          //fprintf(stdout, "\nOVERFLOW HAPPENED\n");
          BTLeafNode sibling;
          int siblingKey = -1;

          //First set the pid
          int newPid = pf.endPid();
          sibling.setNextNodePtr(leafNode.getNextNodePtr());
          leafNode.setNextNodePtr(newPid);


          error = leafNode.insertAndSplit(key, rid, sibling, siblingKey);
          /*
          fprintf(stdout, "Sibling Key %d\n", siblingKey);
          fprintf(stdout, "\nPrinting out all the keys in current node ");
          leafNode.print();
          fprintf(stdout, "\nPrinting out all the keys in sibling node ");
          sibling.print();
          */
          if (error != 0){
            fprintf(stdout, "Error happened at line 156 %d\n", error);
            return error;
          }

          specialKey = siblingKey;
          specialPid = newPid;

          error = sibling.write(newPid, pf);
          if(error){
            return error;
          }
          error = leafNode.write(currentPid, pf);
          if (error){
            fprintf(stdout, "Error happened at line 169 %d \n", error);
            return error;
          }
          if(height == 1){
            BTNonLeafNode root;
            root.initializeRoot(currentPid, siblingKey,newPid);
            rootPid = pf.endPid();
            root.write(rootPid, pf);
            treeHeight++;
          }
          return 0;
        }else{
          return error;
        }

        //We are still at a non-leaf node
    }else{
        BTNonLeafNode nonLeaf;
        error = nonLeaf.read(currentPid, pf);
        if (error){
          fprintf(stdout, "Error happened at line 190 %d\n", error);
          return error;
        }


        PageId nextPid;
        nonLeaf.locateChildPtr(key, nextPid);

        error = insertRecur(key, rid, height+1, nextPid, specialKey, specialPid);
        if (error != 0){
          fprintf(stdout, "Error happened at line 200 %d\n", error);
          return error;
        }
        if (specialKey != -1 && specialPid != -1){
          //We had overflow from previous one
          error = nonLeaf.insert(specialKey, specialPid);

          /*
          fprintf(stdout, "\nPrint out the non leaf node %d\n", currentPid);
          nonLeaf.print();
          */
          if(error == 0){
            nonLeaf.write(currentPid, pf);
            return 0;
          }
          else if(error == RC_NODE_FULL){
            BTNonLeafNode sibling;

            int siblingKey;
            nonLeaf.insertAndSplit(specialKey, specialPid, sibling, siblingKey);
            int newPid = pf.endPid();
            specialKey = siblingKey;
            specialPid = newPid;

            /*
            fprintf(stdout, "\nPrint out the non leaf node %d\n", currentPid);
            nonLeaf.print();

            fprintf(stdout, "\nPrint out the sibling %d\n", newPid);
            sibling.print();
            */
            error = nonLeaf.write(currentPid, pf);
            if (error)
              return error;
            error = sibling.write(newPid, pf);
            if (error)
              return error;
            if(height == 1){
              BTNonLeafNode root;
              root.initializeRoot(currentPid, siblingKey,newPid);
              rootPid = pf.endPid();
              root.write(rootPid, pf);
              treeHeight++;
            }
            return 0;
          }
          else{
            return error;
          }
        }
        //Keep into account that the children nodes might overflow
    }
    return 0;
}

/**
 * Run the standard B+Tree key search algorithm and identify the
 * leaf node where searchKey may exist. If an index entry with
 * searchKey exists in the leaf node, set IndexCursor to its location
 * (i.e., IndexCursor.pid = PageId of the leaf node, and
 * IndexCursor.eid = the searchKey index entry number.) and return 0.
 * If not, set IndexCursor.pid = PageId of the leaf node and
 * IndexCursor.eid = the index entry immediately after the largest
 * index key that is smaller than searchKey, and return the error
 * code RC_NO_SUCH_RECORD.
 * Using the returned "IndexCursor", you will have to call readForward()
 * to retrieve the actual (key, rid) pair from the index.
 * @param key[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @return 0 if searchKey is found. Othewise an error code
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
    //assume that all the searchKey is greater than 0
    if (searchKey < 0){
      return RC_INVALID_ATTRIBUTE;
    }
    if (rootPid != -1){
        return locateRecur(searchKey, cursor, 1, rootPid);
    }else{
        return RC_INVALID_FILE_FORMAT;
    }
}

RC BTreeIndex::locateRecur(int& searchKey, IndexCursor& cursor, int height, PageId& next){
    //Take in your current key
      if (height == treeHeight){
      //We are at the leaf node
        int eid;
        BTLeafNode leafNode;
        RC error = leafNode.read(next, pf);
        error = leafNode.locate(searchKey, eid);
        if(error != 0){
          return error;
        }else{
          cursor.eid = eid;
          cursor.pid = next;
          return 0;
        }
      }
      BTNonLeafNode nonLeaf;
      RC error = nonLeaf.read(next, pf);
      if (error != 0){
        return error;
      }

      error = nonLeaf.locateChildPtr(searchKey, next);
      if (error == 0){
        return locateRecur(searchKey, cursor, height+1, next);
      }
      return error;
  }


/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
    if(cursor.pid <= 0){
      return RC_INVALID_CURSOR;
    }
    BTLeafNode leafNode;
    RC error = leafNode.read(cursor.pid, pf);

    if(error != 0){
      return error;
    }
    int curEid = cursor.eid;
    error = leafNode.readEntry(curEid, key, rid);
    if(error != 0){
      return error;
    }
    if (curEid + 1 >= leafNode.getKeyCount()){
      curEid = 0;
      cursor.pid = leafNode.getNextNodePtr();
    }else{
      curEid ++;
    }
    cursor.eid = curEid;
    return 0;
}

PageId BTreeIndex::getRootPid(){
  return rootPid;
}

int BTreeIndex::getTreeHeight(){
  return treeHeight;
}
