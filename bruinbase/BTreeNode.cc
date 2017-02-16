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
  memset(buffer, '\0', sizeof(buffer));
}

RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
  //Read into the memory buffer
  RC rc;
  if ((rc = pf.read(pid, buffer)) != 0){
    fprintf(stderr, "Failed to read a node from page file into the buffer with error code %d and pid%d \n", rc, pid);
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
void BTLeafNode::setKeyCount(int keyCount){
  int keyCountPos = PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int);
  char* temp = buffer;
  memcpy(temp+keyCountPos, &keyCount, sizeof(int));
}
//I decided to do this because the key could be 0


int BTLeafNode::getKeyCount()
{
    int keyCount;
    int keyCountPos = PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int);
    char* temp = buffer;
    memcpy(&keyCount, temp + keyCountPos, sizeof(int));
    return keyCount;
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
      int MAX_TOTAL_KEY = (PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int)) / keySize;
      char* temp = buffer;
      int lastPid = getNextNodePtr();
    //  fprintf(stdout, "LastPID %d\n", lastPid);
      //key_count = getKeyCount();
      int key_count = getKeyCount();

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
        free(helperBuffer);
    }
    setKeyCount(key_count + 1);
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
  //This should be 1016 / 12 = 84
  //So when overflow happens the total number of keys is 85
  int MAX_TOTAL_KEY = (PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int)) / keySize;
  char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE + keySize);
  int key_count = getKeyCount();

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
  memset(buffer, '\0', PageFile::PAGE_SIZE);
  k = 0;
  temp1 = helperBuffer;
  while(k <= (MAX_TOTAL_KEY+1)/2){
    RecordId temp_rid;
    int temp_key;
    memcpy(&temp_rid, temp1, sizeof(RecordId));
    memcpy(&temp_key, temp1 + sizeof(RecordId), sizeof(int));
    temp1 += keySize;
    insert(temp_key, temp_rid);
    k++;
  }
  setKeyCount((MAX_TOTAL_KEY+1)/2 + 1);
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
  sibling.setKeyCount((MAX_TOTAL_KEY + 1)/2); // set it to 42
  free(helperBuffer);
  return 0;
}

void BTLeafNode::print(){
  //print out the entire B+ Tree Node:
  int key_count = getKeyCount();
  int keySize = sizeof(int) + sizeof(RecordId);
  //This should be 1020 / 12 = 85
  char* temp = buffer + sizeof(RecordId);
  for(int i = 0; i < key_count; i++){
    int currentKey;
    RecordId tempRid;
    memcpy(&tempRid, temp - sizeof(RecordId), sizeof(RecordId));
    memcpy(&currentKey, temp, sizeof(int));
    fprintf(stdout, "%d ", currentKey);
    temp+=keySize;
  }

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

  int j = getKeyCount() - 1;
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
  if(searchKey == tempKey){
    return 0;
  }
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
   int key_count = getKeyCount();
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




void BTNonLeafNode::setKeyCount(int keyCount){
  int keyCountPos = PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int);
  char* temp = buffer;
  memcpy(temp+keyCountPos, &keyCount, sizeof(int));
}
//I decided to do this because the key could be 0


int BTNonLeafNode::getKeyCount()
{
    int keyCount;
    int keyCountPos = PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int);
    char* temp = buffer;
    memcpy(&keyCount, temp + keyCountPos, sizeof(int));
    return keyCount;
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
    int MAX_TOTAL_KEY  = (PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int)) / keySize;
    int key_count = getKeyCount();

    char* temp = buffer + sizeof(PageId);
    // check if the node is full
    if (key_count == MAX_TOTAL_KEY){
      return RC_NODE_FULL;
    }
    if(key_count == 0){
      memcpy(temp, &key, sizeof(int));
      memcpy(temp + sizeof(int), &pid, sizeof(PageId));
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
      memcpy(&tempKey, temp + keySize*i, sizeof(int));
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
      free(helperBuffer);
    }
    //We need to check whether the
    setKeyCount(key_count + 1);
    return 0;
}


RC BTNonLeafNode::getFirstPid(PageId& pid){
  if (pid < 0)
    return RC_INVALID_PID;
  memcpy(&pid, buffer, sizeof(PageId));
  return 0;
}
RC BTNonLeafNode::setFirstPid(PageId& pid){
  if (pid < 0)
    return RC_INVALID_PID;
    memcpy(buffer, &pid, sizeof(PageId));
    return 0;
}

/*
RC BTNonLeafNode::insertAfterOverflow(char* input, int insertlen){
  memcpy(buffer+sizeof(PageId), input, insertlen);
}*/


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

 //There are 127 key-pid pairs in the non-leaf node
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
    int keySize = sizeof(int) + sizeof(PageId);
    //This value is 127
    int MAX_TOTAL_KEY  = (PageFile::PAGE_SIZE - sizeof(PageId) - sizeof(int)) / keySize;

    int key_count = getKeyCount();

    if(sibling.getKeyCount() != 0){
      return RC_INVALID_ATTRIBUTE;
    }
    if(key_count != MAX_TOTAL_KEY){
      return RC_INVALID_FILE_FORMAT;
    }

    if (pid < 0){
      return RC_INVALID_PID;
    }

    char* temp = buffer + sizeof(PageId);
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
    memcpy(&tempKey, temp + keySize*i, sizeof(int));
    if (tempKey < key){
      insert_position = i+1;
    }else{
      insert_position = i;
    }

    //Determined the position to insert
    char* helperBuffer = (char*) malloc(PageFile::PAGE_SIZE + keySize);

    char* temp1 = helperBuffer;
    char* temp2 = buffer;
    memcpy(temp1, temp2, sizeof(PageId));
    temp1 += sizeof(PageId);
    temp2 += sizeof(PageId);

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
    memset(buffer, '\0', PageFile::PAGE_SIZE);
    memcpy(buffer, helperBuffer, sizeof(PageId));
    k = 0;
    temp1 = helperBuffer + sizeof(PageId);
    while(k < MAX_TOTAL_KEY/2 ){
      int temp_key;
      PageId temp_pid;
      memcpy(&temp_key, temp1, sizeof(int));
      memcpy(&temp_pid, temp1 + sizeof(int), sizeof(PageId));
      temp1 += keySize;
      insert(temp_key, temp_pid);
      k++;
    }

    setKeyCount(MAX_TOTAL_KEY/2);
    memcpy(&midKey, temp1 , sizeof(int));
    int nextPid;
    memcpy(&nextPid, temp1 + sizeof(int), sizeof(PageId));
    sibling.setFirstPid(nextPid);
    temp1 += keySize;
    k++;

    RC error;

    while(k < MAX_TOTAL_KEY+1){
      int temp_key;
      PageId temp_pid;
      memcpy(&temp_key, temp1, sizeof(int));
      memcpy(&temp_pid, temp1 + sizeof(int), sizeof(PageId));
      temp1 += keySize;
      error = sibling.insert(temp_key, temp_pid);
      k++;
    }
    sibling.setKeyCount(MAX_TOTAL_KEY/2);
    free(helperBuffer);
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
  char* temp = buffer;
  int key_count = getKeyCount();

  temp = buffer;
  for(int i = 0; i < key_count; i++){
    int current_key;
    memcpy(&current_key, temp+sizeof(PageId), sizeof(int));
    if (current_key > searchKey){
      memcpy(&pid, temp, sizeof(PageId));
      return 0;
    }
    temp += keySize;
  }
  memcpy(&pid, temp, sizeof(PageId));
  return 0;

}


void BTNonLeafNode::print(){
  //print out the entire B+ Tree Node:
  int key_count = getKeyCount();
  int keySize = sizeof(int) + sizeof(PageId);
  //This should be 1020 / 12 = 8
  char* temp = buffer;
   //+ sizeof(PageId);
  fprintf(stdout, "print out pid\n" );
  for(int i = 0; i <= key_count; i++){
    int currentPid;
    memcpy(&currentPid, temp, sizeof(PageId));
    fprintf(stdout, "%d ", currentPid);
    temp+=keySize;
  }
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
  memcpy(temp + sizeof(PageId), &key, sizeof(int));
  memcpy(temp + sizeof(PageId) + sizeof(int), &pid2, sizeof(PageId));
  return 0;
}
