'use strict';

class RandomStore {
  constructor() {
    this.store = this.loadJSON('randomstore') || {};
    this.siloThreshold = 50;
    this.siloCapacity = 100;
    this.makingRequest = false;
  }

  loadJSON(key) {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch {
      return null;
    }
  }

  saveJSON(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  get(sides) {
    const silo = this.getSilo(sides);

    if (!silo) {
      return Math.floor(Math.random() * sides) + 1;
    }

    if (silo.length === 0) {
      this.fillSilo(silo, sides);
      return Math.floor(Math.random() * sides) + 1;
    }

    const num = silo.shift();
    this.saveJSON('randomstore', this.store);
    this.fillSilo(silo, sides);
    return num;
  }

  getSilo(sides) {
    if (this.store.hasOwnProperty(sides.toString())) {
      return this.store[sides.toString()];
    }

    this.makeSilo(sides);
    return false;
  }

  makeSilo(sides) {
    const silo = [];
    this.store[sides] = silo;
    this.fillSilo(silo, sides);
  }

  // top off silo to 100 if it has under 50
  fillSilo(silo, sides) {
    if (silo.length > this.siloThreshold) {
      return;
    }

    const amount = this.siloCapacity - silo.length;

    if (this.makingRequest) {
      return;
    }

    this.makingRequest = true;

    fetch('/api/random', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        n: amount,
        min: 1,
        max: sides,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          this.store[sides] = this.store[sides].concat(data.data);
        }
        this.makingRequest = false;
        this.saveJSON('randomstore', this.store);
      })
      .catch(() => {
        this.makingRequest = false;
      });
  }
}

export default new RandomStore();
