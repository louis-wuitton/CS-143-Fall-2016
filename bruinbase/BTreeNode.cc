#include "BTreeNode.h"
#include <stdio.h>
#include <stdlib.h>
#include <cstring>

using namespace std;

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */

// Goal: implement all the functions in this part

BTLeafNode::BTLeafNode(){
  key_count = 0;
  memset(buffer, '\0', sizeof(buffer));
}

RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
  //Read into the memory buffer
  RC rc;
  if ((rc = pf.read(pid, buffer)) != 0){
    fprintf(stderr, "Failed to read a node from page file into the buffer");
  }
  return rc;
}

/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{
  RC rc;
  //We could assume that previously you have changed the buffer already
  if((rc = pf.write(pid, buffer)) != 0){
    fprintf(stderr, "Failed to write into the page file");
  }
  return rc;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */

//We want to fill into the a node as much keys as possible

int BTLeafNode::getKeyCount()
{
    return key_count;
}


/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */

//each page has a size of 1024
//each pair has a size of 12
//reserve 4 bytes for pageid

//84 or 85 keys in a node


RC BTLeafNode::insert(int key, const RecordId& rid)
{
      int keySize = sizeof(int) + sizeof(RecordId);
      //This should be 1020 / 12 = 85
      int MAX_TOTAL_KEY = (PageFile::PAGE_SIZE - sizeof(PageId)) / keySize;

      char* temp = buffer;
      int lastPid = getNextNodePtr();

      if(key_count == MAX_TOTAL_KEY){
        return RC_NODE_FULL;
      }else if (key_count == 0){
        memcpy(temp, &rid, sizeof(RecordId));
        memcpy(temp + sizeof(RecordId), &key, sizeof(int));
      }else{
        int insert_position;
        if (locate(key, insert_position) == 0){
          return RC_INVALID_ATTRIBUTE;
        }
      //Helper Buffer for insert
        char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE);
        memset(helperBuffer, '\0', PageFile::PAGE_SIZE);
        memcpy(helperBuffer, temp + insert_position*keySize, PageFile::PAGE_SIZE - keySize*insert_position);
        memcpy(temp + insert_position*keySize, &rid, sizeof(RecordId));
        memcpy(temp + insert_position*keySize + sizeof(RecordId), &key, sizeof(int));

        memcpy(temp + (insert_position+1)*keySize, helperBuffer, PageFile::PAGE_SIZE - (insert_position+1)*keySize);
    }

    setNextNodePtr(lastPid);
    key_count++;
    return 0;
}



/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid,
                              BTLeafNode& sibling, int& siblingKey)
{

  if(sibling.getKeyCount() != 0){
    return RC_INVALID_ATTRIBUTE;
  }

  int keySize = sizeof(int) + sizeof(RecordId);
  //This should be 1020 / 12 = 85
  int MAX_TOTAL_KEY = (PageFile::PAGE_SIZE - sizeof(PageId)) / keySize;
  char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE + keySize);

  if(key_count != MAX_TOTAL_KEY){
    return RC_INVALID_FILE_FORMAT;
  }

  int insert_position;
  if (locate(key, insert_position) == 0){
    return RC_INVALID_ATTRIBUTE;
  }

  char* temp1 = helperBuffer;
  char* temp2 = buffer;
  int k;
  for (k = 0; k < insert_position; k++){
    memcpy(temp1, temp2, keySize);
    temp1 += keySize;
    temp2 += keySize;
  }
  //temp = buffer;
  memcpy(temp1, &rid, sizeof(RecordId));
  temp1 += sizeof(RecordId);
  memcpy(temp1, &key, sizeof(int));
  temp1 += sizeof(int);
  k++;

  while(k < MAX_TOTAL_KEY + 1){
    memcpy(temp1, temp2, keySize);
    temp1 += keySize;
    temp2 += keySize;
    k++;
  }
  //Clear the buffer
  key_count = 0;
  memset(buffer, '\0', PageFile::PAGE_SIZE);
  k = 0;
  temp1 = helperBuffer;
  while(k < (MAX_TOTAL_KEY+1)/2){
    RecordId temp_rid;
    int temp_key;
    memcpy(&temp_rid, temp1, sizeof(RecordId));
    memcpy(&temp_key, temp1 + sizeof(RecordId), sizeof(int));
    temp1 += keySize;
    insert(temp_key, temp_rid);
    k++;
  }

  memcpy(&siblingKey, temp1 + sizeof(RecordId), sizeof(int));
  while(k < MAX_TOTAL_KEY + 1){
    RecordId temp_rid;
    int temp_key;
    memcpy(&temp_rid, temp1, sizeof(RecordId));
    memcpy(&temp_key, temp1 + sizeof(RecordId), sizeof(int));
    temp1 += keySize;
    sibling.insert(temp_key, temp_rid);
    k++;
  }
  return 0;
}




/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{
  int keySize = sizeof(int) + sizeof(RecordId);
  int i = 0;
  int j = key_count - 1;
  int middle = 0;
  int insert_position;
  char* temp = buffer;

  while (i < j){
      middle = (i+j) / 2;
      int middleKey;
      memcpy(&middleKey, temp + keySize*middle + sizeof(RecordId), sizeof(int));
      if (middleKey == searchKey){
          eid = middle;
          return 0;
      }
      else if (middleKey < searchKey){
          i = middle + 1;
      }else{
          j = middle - 1;
      }
  }
  int tempKey;
  memcpy(&tempKey, temp + keySize*i + sizeof(RecordId), sizeof(int));
  if (tempKey < searchKey){
    insert_position = i+1;
  }else{
    insert_position = i;
  }
  eid = insert_position;
  return RC_NO_SUCH_RECORD;
}



/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{
   int keySize = sizeof(int) + sizeof(RecordId);
   char* temp = buffer;
   if(eid >= key_count || eid < 0)
      return RC_INVALID_CURSOR;

   memcpy(&rid, temp + eid*keySize, sizeof(RecordId));
   memcpy(&key, temp + eid*keySize + sizeof(RecordId), sizeof(int));
   return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node
 */
PageId BTLeafNode::getNextNodePtr()
{
  char* temp = buffer;
  int lastPointer = PageFile::PAGE_SIZE - sizeof(PageId);
  int nextPid;
  memcpy(&nextPid, temp + lastPointer, sizeof(PageId));
  return nextPid;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{
  if(pid < 0)
    return RC_INVALID_PID;
  char* temp = buffer;
  int lastPointer = PageFile::PAGE_SIZE - sizeof(PageId);
  int nextPid;
  memcpy(temp + lastPointer, &pid, sizeof(PageId));
  return 0;
}






/////////////////////////////////////////////////////////////////////////////////////////////////////////

BTNonLeafNode::BTNonLeafNode(){
  key_count = 0;
  memset(buffer, '\0', sizeof(buffer));
}


/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{
  RC rc;
  if ((rc = pf.read(pid, buffer)) != 0){
    fprintf(stderr, "Failed to read a node from page file into the buffer");
  }
  return rc;
}
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{
  RC rc;
  //We could assume that previously you have changed the buffer already
  if((rc = pf.write(pid, buffer)) != 0){
    fprintf(stderr, "Failed to write into the page file");
  }
  return rc;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
  return key_count;
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{
    int keySize = sizeof(int) + sizeof(PageId);
    int MAX_TOTAL_KEY  = (PageFile::PAGE_SIZE - sizeof(PageId)) / keySize;

    char* temp = buffer + sizeof(PageId);
    // check if the node is full
    if (key_count == MAX_TOTAL_KEY){
      return RC_NODE_FULL;
    }

    if(key_count == 0){
      memcpy(temp, &pid, sizeof(PageId));
      memcpy(temp + sizeof(PageId), &key, sizeof(int));
    }else{
      int i = 0;
      int j = key_count - 1;
      int middle = 0;
      int insert_position;
      while (i < j){
          middle = (i+j) / 2;
          int middleKey;
          memcpy(&middleKey, temp + keySize*middle, sizeof(int));
          if (middleKey == key){
              return RC_INVALID_ATTRIBUTE;
          }
          else if (middleKey < key){
              i = middle + 1;
          }else{
              j = middle - 1;
          }
      }
      int tempKey;
      memcpy(&tempKey, temp + keySize*i + sizeof(RecordId), sizeof(int));
      if (tempKey < key){
        insert_position = i+1;
      }else{
        insert_position = i;
      }
      char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE);
      memset(helperBuffer, '\0', PageFile::PAGE_SIZE);
      int rest_size = PageFile::PAGE_SIZE - keySize*insert_position - sizeof(PageId);
      memcpy(helperBuffer, temp + insert_position*keySize, rest_size);
      memcpy(temp + insert_position*keySize, &key, sizeof(int));
      memcpy(temp + insert_position*keySize + sizeof(int), &pid, sizeof(PageId));

      memcpy(temp + (insert_position+1)*keySize, helperBuffer, rest_size);
    }
    //We need to check whether the
    key_count++;
    return 0;
}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
    int keySize = sizeof(int) + sizeof(PageId);
    int MAX_TOTAL_KEY  = (PageFile::PAGE_SIZE - sizeof(PageId)) / keySize;

    if(sibling.getKeyCount() != 0){
      return RC_INVALID_ATTRIBUTE;
    }
    if (pid < 0){
      return RC_INVALID_PID;
    }

    char* temp = buffer;
    if(key_count != MAX_TOTAL_KEY){
      return RC_INVALID_FILE_FORMAT;
    }

    //Determine where to insert
    int i = 0;
    int j = key_count - 1;
    int middle = 0;
    int insert_position;
    while (i < j){
        middle = (i+j) / 2;
        int middleKey;
        memcpy(&middleKey, temp + keySize*middle, sizeof(int));
        if (middleKey == key){
            return RC_INVALID_ATTRIBUTE;
        }
        else if (middleKey < key){
            i = middle + 1;
        }else{
            j = middle - 1;
        }
    }
    int tempKey;
    memcpy(&tempKey, temp + keySize*i + sizeof(RecordId), sizeof(int));
    if (tempKey < key){
      insert_position = i+1;
    }else{
      insert_position = i;
    }

    //Determined the position to insert
    char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE + keySize);

    //Skip the first page id
    char* temp1 = helperBuffer + sizeof(PageId);
    char* temp2 = buffer + sizeof(PageId);


    int k;
    for (k = 0; k < insert_position; k++){
      memcpy(temp1, temp2, keySize);
      temp1 += keySize;
      temp2 += keySize;
    }


    //temp = buffer;
    memcpy(temp1, &key, sizeof(int));
    temp1 += sizeof(int);
    memcpy(temp1, &pid, sizeof(PageId));
    temp1 += sizeof(PageId);
    k++;


    while(k < MAX_TOTAL_KEY + 1){
      memcpy(temp1, temp2, keySize);
      temp1 += keySize;
      temp2 += keySize;
      k++;
    }


    //Clear the buffer
    key_count = 0;
    memset(buffer, '\0', PageFile::PAGE_SIZE);


    k = 0;
    temp1 = helperBuffer + 4;
    while(k < (MAX_TOTAL_KEY+1)/2){
      int temp_key;
      PageId temp_pid;
      memcpy(&temp_key, temp1, sizeof(int));
      memcpy(&temp_pid, temp1 + sizeof(int), sizeof(PageId));
      temp1 += keySize;
      insert(temp_key, temp_pid);
      k++;
    }

    memcpy(&midKey, temp1 , sizeof(int));
    temp1 += keySize;

    while(k < MAX_TOTAL_KEY + 1){
      int temp_key;
      PageId temp_pid;
      memcpy(&temp_key, temp1, sizeof(int));
      memcpy(&temp_pid, temp1 + sizeof(int), sizeof(PageId));
      temp1 += keySize;
      sibling.insert(temp_key, temp_pid);
      k++;
    }
    return 0;
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{
  int keySize = sizeof(int) + sizeof(PageId);
  char* temp = buffer + 4;
  for(int i = 4; i < getKeyCount()*keySize; i+= keySize){
    int current_key;
    memcpy(&current_key, temp, sizeof(int));
    if (current_key >= searchKey){
      memcpy(&pid, temp-sizeof(PageId), sizeof(PageId));
      return 0;
    }
    temp += keySize;
  }
  memcpy(&pid, temp-sizeof(PageId), sizeof(PageId));
  return 0;

}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{
  //clear the buffer
  memset(buffer, '\0', sizeof(buffer));
  char* temp = buffer;
  memcpy(temp, &pid1, sizeof(PageId));
  RC error = insert(key, pid2);
  if (error != 0){
    return error;
  }
  return 0;
}
